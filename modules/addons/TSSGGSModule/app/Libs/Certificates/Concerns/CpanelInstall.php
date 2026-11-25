<?php

namespace TSSGGSModule\App\Libs\Certificates\Concerns;

use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\AutoInstall\Cpanel;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * Installation of an issued certificate on the client's cPanel account and the remembered install target.
 * Part of CertificateService (used there as a trait).
 */
trait CpanelInstall
{
    /**
     * Installs the issued certificate on the client's cPanel account hosting $domain. The private key is the one
     * stored for AutoInstall or the one the client pastes. The service is enrolled in AutoInstall afterwards,
     * so reissues and renewals are installed automatically.
     */
    public static function installOnCpanel($serviceId, $domain, $privateKey = '')
    {
        $domain = strtolower(trim((string)$domain));
        $userId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid');
        $bundle = self::bundle($serviceId);
        $state  = AutoInstallHelpers::get($serviceId) ?: [];

        $privateKey = trim((string)$privateKey);
        if($privateKey === '')
        {
            $privateKey = self::privateKeyForCertificate($serviceId);
        }
        if($privateKey === '')
        {
            throw new \Exception('The private key of this certificate is required');
        }
        if(!@openssl_x509_check_private_key($bundle->crt(), $privateKey))
        {
            throw new \Exception('The private key does not match the certificate');
        }

        $cpanelService = Cpanel::getServiceByDomain($userId, $domain);
        if($cpanelService === false)
        {
            throw new \Exception('No cPanel hosting account with the domain ' . $domain . ' was found');
        }

        $cpanel = new Cpanel();
        $cpanel->setService($cpanelService);
        $cpanel->installSSL($cpanelService->user, $domain, $bundle->crt(), $privateKey, $bundle->ca());

        $stored = self::stored($serviceId);
        $state  = array_merge($state, [
            'install_type'       => 'autoinstall',
            'domain'             => $domain,
            'common_name'        => $stored['orderData']['orderData']['common_name']['name'] ?? $domain,
            'private_key'        => \encrypt($privateKey),
            'installed_crt_hash' => sha1(trim($bundle->crt())),
            'installed_at'       => date('Y-m-d H:i:s'),
            'valid_till'         => $stored['orderFiles']['validity']['end'] ?? '',
            'last_error'         => '',
        ]);
        if(!AutoInstallHelpers::get($serviceId))
        {
            AutoInstallHelpers::create($serviceId, $state);
        }
        AutoInstallHelpers::save($serviceId, $state, AutoInstallHelpers::STATUS_INSTALLED);

        self::event($serviceId, 'success', '[Install] Certificate installed on cPanel - domain: ' . $domain . ' | server: ' . $cpanelService->hostname, $userId);
    }

    // ---- Install target chosen by the administrator for services without AutoInstall ---- //

    public static function installTarget($serviceId)
    {
        $value = Request::where('serviceid', (int)$serviceId)->where('name', 'installTarget')->value('request');

        return $value === null || $value === '' ? null : (string)$value;
    }

    public static function setInstallTarget($serviceId, $domain)
    {
        $domain = strtolower(trim((string)$domain));
        if($domain === '')
        {
            Request::where('serviceid', (int)$serviceId)->where('name', 'installTarget')->delete();

            return;
        }
        if(!preg_match('/^[a-z0-9.-]+\.[a-z0-9-]+$/', $domain))
        {
            throw new \InvalidArgumentException('Install target "' . $domain . '" is not a valid domain');
        }
        Request::updateOrInsert(['serviceid' => (int)$serviceId, 'name' => 'installTarget'],
            ['request' => $domain, 'status' => 'set', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        self::event($serviceId, 'info', 'Install target set to ' . $domain . ' by the administrator');
    }

    // ---- Renewal "additional days" (days of the current certificate carried over to the renewal) ---- //
}
