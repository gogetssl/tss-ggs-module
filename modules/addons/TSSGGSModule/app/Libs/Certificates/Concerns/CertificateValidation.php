<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates\Concerns;

use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Domain validation of a pending order: missing validation data, approver e-mail, recheck, method change.
 * Part of CertificateService (used there as a trait).
 */
trait CertificateValidation
{
    /**
     * True while a pending order lacks what the client needs to validate: no domains yet, or a domain on a file / DNS
     * method without its value. $stored = Request::getOrder() (['orderData' => .., 'orderFiles' => ..]).
     */
    public static function validationDataMissing(array $stored)
    {
        $status = isset($stored['orderData']['order']['status']) ? (string)$stored['orderData']['order']['status'] : '';
        // only an order the vendor is processing has validation details to wait for: any other status (issued,
        // ended, or waiting at the vendor side: new_order, unpaid, failed) is not read again from the client page
        if(!in_array(\ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers::normalizeStatus($status), ['', 'pending', 'processing'], true))
        {
            return false;
        }

        $items   = isset($stored['orderData']['items']) && is_array($stored['orderData']['items']) ? $stored['orderData']['items'] : [];
        $item    = $items ? reset($items) : [];
        $domains = isset($item['domains']) && is_array($item['domains']) ? $item['domains'] : [];
        if(!$domains)
        {
            return true;
        }

        foreach($domains as $domain)
        {
            $method = strtolower((string)($domain['method'] ?? ''));
            if(!empty($domain['approved']) || $method === '' || $method === 'email' || strpos($method, '@') !== false)
            {
                continue;
            }
            if(empty($domain['validation']['value']))
            {
                return true;
            }
        }

        return false;
    }

    public static function refreshIfIncomplete($serviceId, array $stored)
    {
        if(!self::validationDataMissing($stored) || !self::serviceOpen($serviceId))
        {
            return $stored;
        }

        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->first();
        if($row && !empty($row->updated_at) && strtotime($row->updated_at) > time() - self::REFRESH_SECONDS)
        {
            return $stored;
        }

        try
        {
            self::refresh($serviceId);
        }
        catch(\Throwable $e)
        {
            return $stored;
        }

        $fresh = Request::getOrder($serviceId);

        return is_array($fresh) && $fresh ? $fresh : $stored;
    }

    public static function resendApproverEmail($serviceId)
    {
        $ssl = self::requireOrder($serviceId);
        self::api($serviceId)->resendValidationEmail((int)$ssl->remoteid);
        self::event($serviceId, 'info', 'Validation email resent');
    }

    public static function revalidate($serviceId)
    {
        $ssl = self::requireOrder($serviceId);
        self::api($serviceId)->recheckValidation((int)$ssl->remoteid);
        self::event($serviceId, 'info', 'Domain validation recheck requested');

        return self::refresh($serviceId);
    }

    /**
     * Changes the validation method / approver email of pending domains: [domain => 'http'|'https'|'dns'|email]
     */
    public static function changeValidation($serviceId, array $methods)
    {
        $ssl    = self::requireOrder($serviceId);
        $api    = self::api($serviceId);
        $stored = self::stored($serviceId);

        foreach($methods as $domain => $method)
        {
            $api->changeApprovers((int)$ssl->remoteid, ['domain' => ['name' => $domain, 'validation_method' => $method]]);
            if(isset($stored['orderData']['orderData']['common_name']['name']) && $stored['orderData']['orderData']['common_name']['name'] === $domain)
            {
                $stored['orderData']['orderData']['common_name']['validation_method'] = $method;
            }
            foreach(isset($stored['orderData']['orderData']['alternative_names']) ? $stored['orderData']['orderData']['alternative_names'] : [] as $i => $san)
            {
                if(($san['name'] ?? '') === $domain)
                {
                    $stored['orderData']['orderData']['alternative_names'][$i]['validation_method'] = $method;
                }
            }
        }

        // the change went through; if the gateway can not show the order yet, the stored status stays until the sync reads it
        $fresh = $api->getOrderOr((int)$ssl->remoteid, $serviceId, ['order' => $stored['orderData']['order'] ?? [], 'items' => $stored['orderData']['items'] ?? []]);
        $stored['orderData']['order'] = $fresh['order'];
        $stored['orderData']['items'] = $fresh['items'];
        Request::updateOrderData($serviceId, $stored);

        self::event($serviceId, 'info', '[Validation] Validation method changed: ' . implode(', ', array_map(function($d, $m) {
            return $d . ' => ' . $m;
        }, array_keys($methods), $methods)));
    }
}
