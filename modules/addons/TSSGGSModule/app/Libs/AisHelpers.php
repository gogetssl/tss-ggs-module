<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;

class AisHelpers
{
    public static function isAisWhmcsProduct($whmcsProductId)
    {
        $product = Product::find($whmcsProductId);
        if(!$product || empty($product->configoption2))
        {
            return false;
        }

        $remoteProduct = RemoteProduct::where('remoteId', $product->configoption2)->first();

        return $remoteProduct && strtolower((string)$remoteProduct->category) === 'ais';
    }

    public static function refreshAisDetails($serviceId)
    {
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->ais_item_id <= 0)
        {
            return;
        }

        $api     = Helpers::getApiByServiceId($serviceId);
        $details = $api->getAisDetails((int)$subscription->ais_item_id);

        $orderStatus = isset($details['order']['status']) ? $details['order']['status'] : $subscription->status;
        $items       = isset($details['items']) && is_array($details['items']) ? $details['items'] : [];
        $firstItem   = isset($items[0]) ? (array)$items[0] : [];
        $autoinstall = isset($firstItem['autoinstall']) ? (array)$firstItem['autoinstall'] : [];
        $subData     = isset($firstItem['subscription']) ? (array)$firstItem['subscription'] : [];

        $subscription->status             = $orderStatus;
        $subscription->login_sso_link     = isset($autoinstall['login_sso_link'])  ? $autoinstall['login_sso_link']  : $subscription->login_sso_link;
        $subscription->manage_sso_link    = isset($autoinstall['manage_sso_link']) ? $autoinstall['manage_sso_link'] : $subscription->manage_sso_link;
        $subscription->autoinstall_status = isset($autoinstall['status'])          ? $autoinstall['status']          : $subscription->autoinstall_status;
        $subscription->period_start       = isset($subData['begin'])        ? ($subData['begin'] ?: null)        : $subscription->period_start;
        $subscription->period_end         = isset($subData['next_renewal']) ? ($subData['next_renewal'] ?: null) : $subscription->period_end;
        $subscription->renewal_date       = isset($subData['next_renewal']) ? ($subData['next_renewal'] ?: null) : $subscription->renewal_date;
        $subscription->save();

        $sslOrder = SSL::getByServiceId($serviceId);
        if($sslOrder)
        {
            $sslOrder->status = ($orderStatus === 'active') ? 'Completed' : 'Configuration Submitted';
            $sslOrder->setSSLStatus($orderStatus);
            $sslOrder->save();
        }
    }
}
