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

    /** install methods of a Plan + Automate subscription */
    const METHOD_AUTOINSTALL = 'autoinstall';
    const METHOD_ACME        = 'acme';

    /**
     * The gateway does not switch the install method yet (requested from the gateway team, 2026-11-13): it ignores the
     * method on a new order and has no switch call. While false the client sees neither the choice nor the switch.
     */
    const METHOD_SWITCH_AVAILABLE = false;

    protected static $columnsChecked = false;

    /**
     * Columns added in 1.4 (install method, ACME account of a subscription switched to ACME). The schema file only
     * creates missing tables, so an existing table gets them here.
     */
    public static function ensureColumns()
    {
        if(self::$columnsChecked)
        {
            return;
        }
        self::$columnsChecked = true;
        $table  = (new AisSubscription())->getTable();
        $schema = \WHMCS\Database\Capsule::schema();
        if(!$schema->hasTable($table))
        {
            return;
        }
        $columns = ['install_method' => 'string', 'acme_account_id' => 'string', 'eab_kid' => 'string', 'eab_hmac_key' => 'text', 'server_url' => 'text'];
        foreach($columns as $column => $type)
        {
            if(!$schema->hasColumn($table, $column))
            {
                $schema->table($table, function($t) use ($column, $type) {
                    $type === 'text' ? $t->text($column)->nullable() : $t->string($column, $column === 'install_method' ? 32 : 255)->nullable();
                });
            }
        }
    }

    public static function refreshAisDetails($serviceId)
    {
        self::ensureColumns();
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
        // auto-renewal off (here or at the vendor): the gateway gives the end of the period instead of a renewal date
        if(empty($subData['next_renewal']) && !empty($subData['end']))
        {
            $subscription->period_end = $subData['end'];
            $subscription->auto_renew = 0;
        }
        // the method the vendor reports (none reported: AutoInstall SSL, the default)
        $method = strtolower((string)($firstItem['install_method'] ?? ($autoinstall['installation_method'] ?? '')));
        $subscription->install_method = strpos($method, 'acme') !== false ? self::METHOD_ACME : self::METHOD_AUTOINSTALL;
        // switched to ACME: the ACME account the client's ACME client registers with
        $account = isset($firstItem['account']) ? (array)$firstItem['account'] : [];
        if($account)
        {
            $subscription->acme_account_id = isset($account['id']) ? (string)$account['id'] : $subscription->acme_account_id;
            $subscription->eab_kid         = isset($account['eab_mac_id']) ? (string)$account['eab_mac_id'] : $subscription->eab_kid;
            $subscription->eab_hmac_key    = isset($account['eab_mac_key']) ? (string)$account['eab_mac_key'] : $subscription->eab_hmac_key;
            $subscription->server_url      = isset($account['server_url']) ? (string)$account['server_url'] : $subscription->server_url;
        }
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
