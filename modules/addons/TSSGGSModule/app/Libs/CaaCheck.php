<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * CAA DNS records (RFC 8659): a domain owner lists the certificate authorities that may issue for the domain. A record
 * that does not list the product's authority stops the order at the CA without any message, so the module warns:
 * - on the validation step of the configuration (before the order is placed; the client can continue),
 * - on the pending certificate page and the admin service tab,
 * from its own DNS lookup and from the gateway's flag (items[].caa.status = 1: the CA's own check failed).
 */
class CaaCheck
{
    /** CAA identifiers each authority accepts, the first one is the one suggested */
    const IDENTIFIERS = [
        'sectigo'  => ['sectigo.com', 'comodoca.com', 'comodo.com', 'usertrust.com', 'trust-provider.com'],
        'digicert' => ['digicert.com', 'www.digicert.com', 'digitalcertvalidation.com', 'symantec.com', 'geotrust.com', 'rapidssl.com', 'thawte.com'],
    ];
    const NAMES = ['sectigo' => 'Sectigo', 'digicert' => 'DigiCert'];

    /** tags a CA understands; an unknown tag with the critical flag stops issuance */
    const KNOWN_TAGS = ['issue', 'issuewild', 'iodef', 'contactemail', 'contactphone', 'issuevmc', 'issuemail'];

    /** gateway flag already logged for a service */
    const LOGGED = 'caaWarned';

    /** @var array name => CAA records ([] none, null lookup failed) */
    protected static $cache = [];

    /** 'sectigo' | 'digicert' | null (unknown authority: no check). GoGetSSL-brand certificates are issued by Sectigo */
    public static function authority($packageId)
    {
        $authority = CertificateService::authority((int)$packageId);

        return $authority === 'gogetssl' ? 'sectigo' : (isset(self::IDENTIFIERS[$authority]) ? $authority : null);
    }

    /**
     * The domains whose CAA records stop the authority, each: domain, wildcard, at (the name holding the records),
     * records (as found), tag (issue | issuewild) and value (the record to add)
     */
    public static function blocked($authority, array $domains)
    {
        $result = [];
        if(!isset(self::IDENTIFIERS[$authority]) || !defined('DNS_CAA'))
        {
            return $result;
        }
        foreach(array_unique(array_map(function($d) { return strtolower(trim((string)$d)); }, $domains)) as $domain)
        {
            if($domain === '' || filter_var($domain, FILTER_VALIDATE_IP))
            {
                continue;
            }
            $found = self::check($domain, $authority);
            if($found)
            {
                $result[] = $found;
            }
        }

        return $result;
    }

    /** null when the authority may issue (or the lookup failed), otherwise the details of the blocking records */
    public static function check($domain, $authority)
    {
        $wildcard = strpos($domain, '*.') === 0;
        $name     = $wildcard ? substr($domain, 2) : $domain;
        // the closest name up the tree that has CAA records decides (RFC 8659 section 3)
        $labels = explode('.', rtrim($name, '.'));
        $set    = [];
        $at     = '';
        for($i = 0; $i < count($labels) - 1; $i++)
        {
            $candidate = implode('.', array_slice($labels, $i));
            $records   = self::records($candidate);
            if($records === null)
            {
                return null; // DNS could not be asked: no warning rather than a wrong one
            }
            if($records)
            {
                $set = $records;
                $at  = $candidate;
                break;
            }
        }
        if(!$set)
        {
            return null;
        }

        $tag      = $wildcard && array_filter($set, function($r) { return $r['tag'] === 'issuewild'; }) ? 'issuewild' : 'issue';
        $relevant = array_filter($set, function($r) use ($tag) { return $r['tag'] === $tag; });
        $critical = array_filter($set, function($r) { return ($r['flags'] & 128) && !in_array($r['tag'], self::KNOWN_TAGS, true); });
        $allowed  = !$relevant && !$critical;
        foreach($relevant as $record)
        {
            $identifier = strtolower(trim(explode(';', $record['value'])[0]));
            if(in_array($identifier, self::IDENTIFIERS[$authority], true))
            {
                $allowed = !$critical;
                break;
            }
        }
        if($allowed)
        {
            return null;
        }

        return [
            'domain'    => $domain,
            'wildcard'  => $wildcard,
            'at'        => $at,
            'records'   => array_values(array_map(function($r) { return $r['flags'] . ' ' . $r['tag'] . ' "' . $r['value'] . '"'; }, $set)),
            'tag'       => $wildcard ? 'issuewild' : 'issue',
            'value'     => '0 ' . ($wildcard ? 'issuewild' : 'issue') . ' "' . self::IDENTIFIERS[$authority][0] . '"',
            'authority' => self::NAMES[$authority],
        ];
    }

    /** CAA records of one name: [['flags', 'tag', 'value']], [] when it has none, null when DNS could not be asked */
    protected static function records($name)
    {
        if(array_key_exists($name, self::$cache))
        {
            return self::$cache[$name];
        }
        $answer = @dns_get_record($name, DNS_CAA);
        $result = null;
        if(is_array($answer))
        {
            $result = [];
            foreach($answer as $row)
            {
                if(($row['type'] ?? '') === 'CAA' && isset($row['tag']))
                {
                    $result[] = ['flags' => (int)($row['flags'] ?? 0), 'tag' => strtolower((string)$row['tag']), 'value' => (string)($row['value'] ?? '')];
                }
            }
        }

        return self::$cache[$name] = $result;
    }

    /** The gateway reports that the CA's own CAA check failed (items[].caa.status = 1) */
    public static function gatewayFlag(array $orderData)
    {
        foreach((array)($orderData['items'] ?? []) as $item)
        {
            if((int)($item['caa']['status'] ?? 0) === 1)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Warnings of a pending certificate: ['blocked' => [...], 'gateway' => bool], or null when there is nothing to say
     * (issued, cancelled, unknown authority)
     * @param array $domains names not validated yet
     */
    public static function forService($serviceId, array $domains)
    {
        $stored    = CertificateService::stored($serviceId);
        $orderData = (array)($stored['orderData'] ?? []);
        // only while the certificate waits for issuance
        if(in_array(strtolower((string)($orderData['order']['status'] ?? '')), ['active', 'cancelled', 'canceled', 'rejected', 'revoked', 'refunded', 'expired', 'filed'], true))
        {
            return null;
        }
        $packageId = (int)\WHMCS\Database\Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        $authority = self::authority($packageId);
        $blocked   = $authority ? self::blocked($authority, $domains) : [];
        $gateway   = self::gatewayFlag($orderData);

        return $blocked || $gateway ? ['blocked' => $blocked, 'gateway' => $gateway, 'authority' => $authority ? self::NAMES[$authority] : ''] : null;
    }

    /** After a status sync: the first time the gateway flags CAA, the certificate log says so */
    public static function afterSync($serviceId, array $orderData)
    {
        if(!self::gatewayFlag($orderData) || Request::where('serviceid', (int)$serviceId)->where('name', self::LOGGED)->exists())
        {
            return;
        }
        Request::insert(['serviceid' => (int)$serviceId, 'name' => self::LOGGED, 'request' => date('Y-m-d H:i:s'), 'status' => 'done',
                         'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        CertificateService::event($serviceId, 'error', '[Validation] The certificate authority reports a failed CAA check: a CAA DNS record of the domain does not allow it to issue. The order waits until the record is fixed.');
    }

    /**
     * The warning box. $texts: title, intro (with %s = authority), fix, gateway, extra (optional last line)
     */
    public static function html(array $warning, array $texts)
    {
        $e    = function($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
        $html = '<div class="alert alert-warning tssggs-caa-warning" role="alert">'
            . '<p style="margin-bottom:8px"><strong>' . $e($texts['title']) . '</strong></p>';
        if(!empty($warning['blocked']))
        {
            $html .= '<p style="margin-bottom:8px">' . $e(sprintf($texts['intro'], $warning['blocked'][0]['authority'])) . '</p>'
                . '<table class="table table-sm table-condensed" style="background:transparent;margin-bottom:8px"><tbody>';
            foreach($warning['blocked'] as $row)
            {
                $html .= '<tr><td style="width:35%"><strong>' . $e($row['domain']) . '</strong>'
                    . ($row['at'] !== ltrim($row['domain'], '*.') ? '<br><small>' . $e(sprintf($texts['inherited'], $row['at'])) . '</small>' : '') . '</td>'
                    . '<td><small>' . $e($texts['now']) . '</small><br><code>' . implode('</code><br><code>', array_map($e, $row['records'])) . '</code></td>'
                    . '<td><small>' . $e($texts['add']) . '</small><br><code>' . $e($row['at'] . '. CAA ' . $row['value']) . '</code></td></tr>';
            }
            $html .= '</tbody></table><p style="margin-bottom:0">' . $e($texts['fix']) . '</p>';
        }
        if(!empty($warning['gateway']))
        {
            $html .= '<p style="margin:' . (!empty($warning['blocked']) ? '8px' : '0') . ' 0 0">' . $e($texts['gateway']) . '</p>';
        }
        if(!empty($texts['extra']))
        {
            $html .= '<p style="margin:8px 0 0">' . $e($texts['extra']) . '</p>';
        }

        return $html . '</div>';
    }
}
