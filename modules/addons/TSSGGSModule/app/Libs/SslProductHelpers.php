<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;

class SslProductHelpers
{
    /**
     * Next due date of a certificate or other gateway product: the end of the ORDER.
     *
     * The order (the gateway's "subscription") is what the client pays for - one to three years, as the billing
     * cycle. Inside it the certificate is reissued as often as needed, so the certificate's own expiry says nothing
     * about when the service is due and is never used here. When the gateway gives no order end: null, and the
     * caller keeps the due date WHMCS set. When the renewal is billed is App\Libs\RenewalPolicy's part.
     * Returns Y-m-d or null.
     *
     * @param array $orderFiles unused, kept for the callers' signature
     */
    public static function getServiceDueDate($orderData, $orderFiles = [])
    {
        $items     = isset($orderData['items']) && is_array($orderData['items']) ? $orderData['items'] : [];
        $item      = reset($items);
        $end       = isset($item['subscription']['end']) ? (string)$item['subscription']['end'] : '';
        $timestamp = $end !== '' ? strtotime($end) : false;

        return $timestamp && $timestamp > 0 ? date('Y-m-d', $timestamp) : null;
    }

    /**
     * @deprecated since 1.5.0 the service falls due on the order end itself; always 0
     */
    public static function dueDateOffset($orderData = [])
    {
        return 0;
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
