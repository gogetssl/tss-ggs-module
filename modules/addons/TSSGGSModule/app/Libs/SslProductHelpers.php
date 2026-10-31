<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;

class SslProductHelpers
{
    /**
     * Next due date of an SSL service: end of the subscription (order), not of the current certificate.
     * Multi-year subscriptions get several certificates (reissues) - the service is renewed only when the subscription
     * ends. Falls back to the certificate end when the API does not return the subscription. Returns Y-m-d or null.
     */
    public static function getServiceDueDate($orderData, $orderFiles)
    {
        $items = isset($orderData['items']) && is_array($orderData['items']) ? $orderData['items'] : [];
        $item  = reset($items);

        $candidates = [
            isset($item['subscription']['end']) ? $item['subscription']['end'] : '',
            isset($orderFiles['validity']['end']) ? $orderFiles['validity']['end'] : '',
        ];

        foreach($candidates as $date)
        {
            $timestamp = $date ? strtotime($date) : false;
            if($timestamp && $timestamp > 0)
            {
                return date('Y-m-d', $timestamp);
            }
        }

        return null;
    }

    /**
     * Whether the product itself is a wildcard certificate (main domain *.example.com).
     *
     * The API does not expose a single documented flag, so the check goes from the most to the least reliable source:
     *  1. explicit flags in the synchronized product data (wildcard / is_wildcard / type / features),
     *  2. product pricing - wildcard products are priced under "base.wildcard" only,
     *  3. product name (remote label or WHMCS product name) containing "wildcard".
     */
    public static function isWildcardProduct($remoteProductId, $whmcsProductName = '')
    {
        $remoteProduct = $remoteProductId ? RemoteProduct::where('remoteId', $remoteProductId)->first() : null;
        $rawData       = $remoteProduct && is_array($remoteProduct->rawData) ? $remoteProduct->rawData : [];

        foreach(['wildcard', 'is_wildcard', 'wildcard_enabled'] as $flag)
        {
            if(array_key_exists($flag, $rawData) && !is_array($rawData[$flag]))
            {
                return (bool)$rawData[$flag];
            }
        }

        if(isset($rawData['type']) && is_string($rawData['type']) && stripos($rawData['type'], 'wildcard') !== false)
        {
            return true;
        }

        if(isset($rawData['features']) && is_array($rawData['features']))
        {
            foreach($rawData['features'] as $key => $feature)
            {
                if((is_string($feature) && strtolower($feature) === 'wildcard') || ($key === 'wildcard' && $feature))
                {
                    return true;
                }
            }
        }

        if(isset($rawData['prices']) && is_array($rawData['prices']))
        {
            $hasSingle   = false;
            $hasWildcard = false;
            foreach($rawData['prices'] as $price)
            {
                if(isset($price['base']['single']))
                {
                    $hasSingle = true;
                }
                if(isset($price['base']['wildcard']))
                {
                    $hasWildcard = true;
                }
            }
            if($hasWildcard && !$hasSingle)
            {
                return true;
            }
        }

        $names = [(string)$whmcsProductName, $remoteProduct ? (string)$remoteProduct->name : ''];
        foreach($names as $name)
        {
            if(stripos($name, 'wildcard') !== false)
            {
                return true;
            }
        }

        return false;
    }
}
