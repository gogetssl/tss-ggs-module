<?php

namespace TSSGGSModule\App\Libs\Certificates\Concerns;

use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use TSSGGSModule\App\Libs\Billing\SanUpgrade;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Support\ClientText;
use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\Certificates\FreeName;
use TSSGGSModule\App\Libs\Certificates\IpCertificate;
use TSSGGSModule\App\Libs\Certificates\MainDomain;

/**
 * Reissue and extra SANs: CSR / key checks, the SANs on the certificate now, the reissue call.
 * Part of CertificateService (used there as a trait).
 */
trait CertificateReissue
{
    /**
     * Certificate authorities refuse RSA keys under 2048 bits and EC keys under 256 bits - say so before ordering
     */
    public static function assertCsrKey($csr)
    {
        $key     = @openssl_csr_get_public_key((string)$csr);
        $details = $key ? openssl_pkey_get_details($key) : false;
        if(!$details)
        {
            return;
        }
        $minimum = $details['type'] === OPENSSL_KEYTYPE_EC ? 256 : 2048;
        if((int)$details['bits'] < $minimum)
        {
            throw new \Exception(ClientText::get('csrKeyTooSmall', 'The CSR uses a :bits:-bit key. Certificate authorities need at least 2048-bit RSA or 256-bit ECC - please create a new CSR.', ['bits' => (int)$details['bits']]));
        }
    }

    /**
     * SANs of the current order without the common name (and the free www. / bare twin of it) - what the client
     * paid for and should keep when reissuing: ['single' => [...], 'wildcard' => [...]]
     */
    public static function currentSans($serviceId)
    {
        $stored = self::stored($serviceId);
        $cn     = strtolower((string)($stored['orderData']['orderData']['common_name']['name'] ?? ''));
        $names  = [];

        foreach((array)($stored['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $names[] = is_array($san) ? ($san['name'] ?? ($san['domain'] ?? '')) : (string)$san;
        }
        if(!$names && !empty($stored['orderFiles']))
        {
            $crt    = AutoInstallHelpers::extractCertificate($stored['orderFiles'], $cn)['crt'];
            $parsed = $crt ? openssl_x509_parse($crt) : false;
            foreach(explode(',', (string)($parsed['extensions']['subjectAltName'] ?? '')) as $entry)
            {
                if(stripos(trim($entry), 'DNS:') === 0)
                {
                    $names[] = substr(trim($entry), 4);
                }
            }
        }

        // the free name of the common name: example.com for *.example.com and www.example.com, www.example.com otherwise
        if(strpos($cn, '*.') === 0)
        {
            $twin = substr($cn, 2);
        }
        elseif(strpos($cn, 'www.') === 0)
        {
            $twin = substr($cn, 4);
        }
        else
        {
            $twin = 'www.' . $cn;
        }
        $result = ['single' => [], 'wildcard' => []];
        foreach(array_unique(array_map('strtolower', array_map('trim', $names))) as $name)
        {
            if($name === '' || $name === $cn || $name === $twin)
            {
                continue;
            }
            $result[strpos($name, '*.') === 0 ? 'wildcard' : 'single'][] = $name;
        }

        return $result;
    }

    /**
     * Buys additional SANs on the issued certificate - the client adds the names with the next reissue
     */
    public static function addSans($serviceId, $single, $wildcard)
    {
        $ssl = self::requireOrder($serviceId);
        // always send both counts - the GoGetSSL gateway fails ("san_w_count cannot be null") when one is left out
        $data = ['single_san' => max(0, (int)$single), 'wildcard_san' => max(0, (int)$wildcard)];
        if(!$data['single_san'] && !$data['wildcard_san'])
        {
            return false;
        }

        self::api($serviceId)->addSan((int)$ssl->remoteid, $data);
        self::event($serviceId, 'success', 'Additional SANs ordered: ' . (int)$single . ' single, ' . (int)$wildcard . ' wildcard');

        return true;
    }

    /**
     * Reissues the certificate with a new CSR. $methods: [domain => 'http'|'https'|'dns'|approver email], the common
     * name first. When the CSR uses the private key stored for AutoInstall, the reissued certificate is installed
     * automatically again; any other key turns AutoInstall off for the service.
     */
    public static function reissue($serviceId, $csr, array $methods)
    {
        self::requireUsable($serviceId);
        $ssl     = self::requireOrder($serviceId);
        $subject = @openssl_csr_get_subject($csr);
        // a certificate for public IPs (App\Libs\Certificates\IpCertificate): the CSR has no common name, the main name is the
        // first of the names (the current main name stays first)
        $ipCertificate = IpCertificate::isProduct((int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid'));
        if($ipCertificate)
        {
            if($subject === false || !empty($subject['CN']))
            {
                throw new \Exception('This certificate needs a CSR without a common name');
            }
            $commonName = (string)array_key_first($methods);
            // IP addresses are validated by file only (no mailbox, no DNS zone)
            foreach($methods as $name => $method)
            {
                if(!in_array($method, ['http', 'https'], true))
                {
                    throw new \Exception('Use HTTP or HTTPS file validation for ' . $name);
                }
            }
        }
        else
        {
            if(!$subject || empty($subject['CN']))
            {
                throw new \Exception(ClientText::get('csrNotValid', 'The CSR is not valid'));
            }
            $commonName = $subject['CN'];
        }
        if(empty($methods[$commonName]))
        {
            throw new \Exception(ClientText::get('noValidationMethod', 'No validation method for :domain:', ['domain' => $commonName]));
        }
        // the names come from posted form keys: never trust them beyond the SANs that were paid for
        SanUpgrade::assertWithinLimits($serviceId, $commonName, array_keys($methods));
        // a reissue may not turn the main domain into a wildcard the service has not paid for
        MainDomain::assertCommonName((int)$serviceId, (string)$commonName);

        // the request of the current certificate (after a renewal: the renewal's, not the first order's)
        $request      = Request::currentOrder((int)$serviceId);
        $requestOrder = $request && $request->request ? (json_decode(\decrypt($request->request), true) ?: []) : [];

        $sans = [];
        foreach($methods as $domain => $method)
        {
            // an IP certificate's names are all SANs, the main name too (the vendor needs at least one)
            if($domain !== $commonName || $ipCertificate)
            {
                $sans[] = ['name' => $domain, 'validation_method' => $method];
            }
        }

        // TheSSLStore adds the free www / base-domain name itself: it is neither sent nor stored as a SAN (FreeName)
        if(FreeName::addedForService($serviceId))
        {
            $sans = FreeName::strip($sans, $commonName);
        }

        $api     = self::api($serviceId);
        $reissue = $api->reissueCertificate((int)$ssl->remoteid, [
            'csr'                         => trim($csr),
            'common_name'                 => ['name' => $commonName, 'validation_method' => $methods[$commonName]],
            'existing_alternative_names'  => isset($requestOrder['alternative_names']) ? $requestOrder['alternative_names'] : [],
            'submitted_alternative_names' => $sans,
        ]);
        $itemId = SSLTrustCenterApi::getItemId($reissue, (int)$ssl->remoteid);
        // SANs bought before a reissue can no longer be given back for a refund
        SanUpgrade::noteReissue((int)$serviceId);
        \TSSGGSModule\App\Libs\Certificates\ApproverEmails::replace($serviceId, $methods);

        $requestOrder['csr']               = trim($csr);
        $requestOrder['common_name']       = ['name' => $commonName, 'validation_method' => $methods[$commonName]];
        $requestOrder['alternative_names'] = $sans;
        if($request)
        {
            Request::where('id', (int)$request->id)->update([
                'request'    => \encrypt(json_encode($requestOrder)),
                'status'     => 'pending',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // the reissue was accepted; if the gateway can not show the order yet, the stored details stay until the sync reads it
        $orderData  = $api->getOrderOr($itemId, $serviceId, (array)(self::stored($serviceId)['orderData'] ?? $reissue));
        $orderFiles = [];
        try
        {
            $orderFiles = $api->getCertificateFiles($itemId);
        }
        catch(\Exception $e)
        {
        }
        // the current certificate stays downloadable until the reissued one is out
        self::keepPreviousCertificate($serviceId);
        Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->update([
            'request'    => \encrypt(json_encode(['orderData' => $orderData, 'orderFiles' => $orderFiles])),
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['remoteid' => $itemId]);
        Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['domain' => $commonName]);

        $keepAutoInstall = self::csrUsesAutoInstallKey($serviceId, $csr);
        try
        {
            // made with the module's CSR generator: keep the new key for "Install on server"
            self::rememberGeneratedKey($serviceId, $csr);
            if($keepAutoInstall)
            {
                self::queueAutoInstallAfterReissue($serviceId, $itemId, $methods[$commonName], count(array_filter(array_keys($methods), function($d) { return strpos($d, '*.') === 0; })) > 0);
            }
            else
            {
                AutoInstallHelpers::onManualReissue($serviceId);
            }
        }
        catch(\Throwable $e)
        {
        }

        self::event($serviceId, 'success', '[Reissue] Certificate reissue submitted for ' . $commonName . ' (' . count($methods) . ' domain(s))'
            . ($keepAutoInstall ? ' - it will be installed automatically once issued' : ''));

        return $itemId;
    }

    // ---- Private key of the certificate, kept (encrypted) when the module generated it ---- //

    protected static function queueAutoInstallAfterReissue($serviceId, $itemId, $method, $isWildcard)
    {
        $state  = AutoInstallHelpers::get($serviceId);
        $userId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        $task   = [
            'domain'       => $state['domain'],
            'api_order_id' => (int)$itemId,
            'is_reissue'   => true,
            'is_wildcard'  => $isWildcard || !empty($state['is_wildcard']),
        ];
        if($method === 'dns')
        {
            $task['dns_check_start'] = time();
        }

        AutoInstallQueue::cancelOpenTasks($serviceId, 'Certificate reissued');
        AutoInstallQueue::addTask($userId, $serviceId, 'validate', $task);
        $state['dcv_method'] = strpos($method, '@') !== false ? 'email' : $method;
        $state['last_error'] = '';
        AutoInstallHelpers::save($serviceId, $state, AutoInstallHelpers::STATUS_AWAITING);
    }
}
