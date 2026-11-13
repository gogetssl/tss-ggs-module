<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Lifecycle of the imported products: status badges, hide / show, retire / un-retire,
 * repair of the SAN configurable options and cancellation of all renewals of a product
 */
class ProductCatalog
{
    const OPTIONS = ['single' => ['sans', 'Additional domains (SANs)'], 'wildcard' => ['sans_wildcard', 'Additional wildcard domains (*.example.com)']];

    /** Default option names of earlier versions */
    const OLD_LABELS = ["Additional Single domain SAN's", "Additional Wildcard domain SAN's"];

    /** Gateway product categories the module can sell */
    const SUPPORTED_CATEGORIES = ['tls', 'caas', 'ais', 'smime', 'cs', 'code-guard'];

    /**
     * Products GoGetSSL lists in the "tls" category that are not SSL certificates (document signing, mark certificates)
     */
    const NOT_SSL_NAMES = '/docsign|document\s+signing|\b(common|verified)\s+mark\b|\b(cmc|vmc)\b/i';

    /**
     * The module can sell the gateway product: SSL/TLS, ACME, AutoInstall SSL, S/MIME, GoGetSSL hardware code signing
     * and TheSSLStore CodeGuard. Cloud code signing, document signing, mark certificates and the other website
     * security services (SiteLock, cWatch) are not supported.
     */
    public static function importable($remoteProduct)
    {
        $category = strtolower((string)$remoteProduct->category);
        if(!in_array($category, self::SUPPORTED_CATEGORIES, true))
        {
            return false;
        }
        if($category === SmimeService::CATEGORY)
        {
            return SmimeService::isSupportedRemoteProduct($remoteProduct->vendor, $category);
        }
        if($category === CodeSigningService::CATEGORY)
        {
            return CodeSigningService::isSupportedRemoteProduct($remoteProduct->vendor, $category, $remoteProduct->remoteId);
        }
        if($category === CodeGuardService::CATEGORY)
        {
            return CodeGuardService::isSupportedRemoteProduct($remoteProduct->vendor, $category);
        }
        if($category === 'tls' && preg_match(self::NOT_SSL_NAMES, (string)$remoteProduct->name))
        {
            return false;
        }

        return true;
    }

    /**
     * Name of a SAN configurable option as the client sees it on the order form, with what the price already includes
     */
    public static function optionLabel($kind, $included = 0)
    {
        $label = self::OPTIONS[$kind === 'wildcard' ? 'wildcard' : 'single'][1];

        return $label . ((int)$included > 0 ? ' - ' . (int)$included . ' included in the price' : '');
    }

    /**
     * Status badges of a gateway product and its WHMCS product
     */
    public static function badges(array $remoteRaw, $whmcsProduct)
    {
        $badges = [];
        if(!empty($remoteRaw['missing_since']))
        {
            $badges[] = 'removed';
        }
        if(!$whmcsProduct)
        {
            $badges[] = 'not_imported';

            return $badges;
        }
        $badges[] = 'imported';
        if(!empty($whmcsProduct->retired))
        {
            $badges[] = 'retired';
        }
        elseif(!empty($whmcsProduct->hidden))
        {
            $badges[] = 'hidden';
        }
        if(self::optionProblems((int)$whmcsProduct->id, $remoteRaw))
        {
            $badges[] = 'options';
        }

        return $badges;
    }

    /**
     * The gateway no longer sells the product: the WHMCS product is retired, or the gateway product is gone from the list
     */
    public static function isDiscontinued($packageId)
    {
        $product = Capsule::table('tblproducts')->where('id', (int)$packageId)->first(['retired', 'configoption2']);
        if(!$product)
        {
            return false;
        }
        if(!empty($product->retired))
        {
            return true;
        }
        $remote = \ModulesGarden\TSSGGSModule\App\Models\RemoteProduct::where('remoteId', $product->configoption2)->first();

        return $remote && is_array($remote->rawData) && !empty($remote->rawData['missing_since']);
    }

    /**
     * With a renewal invoice for a discontinued product: the admin gets a warning (activity log, certificate log) and
     * the client the "Product discontinued" e-mail (it can be disabled on the Emails page)
     * @return bool whether the product is discontinued
     */
    public static function noticeIfDiscontinued($serviceId)
    {
        $service = Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['packageid']);
        if(!$service || !self::isDiscontinued($service->packageid))
        {
            return false;
        }

        $text = 'The product of this service is discontinued at the gateway: renewal under the same product may be refused - arrange a replacement certificate';
        GeneralSettings::activity('Service #' . (int)$serviceId . ': ' . $text);
        CertificateService::event((int)$serviceId, 'warning', $text);
        try
        {
            \ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::sendEmail(\ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates::DISCONTINUED_TEMPLATE, (int)$serviceId);
        }
        catch(\Throwable $e)
        {
        }

        return true;
    }

    public static function setHidden($productId, $hidden)
    {
        return Capsule::table('tblproducts')->where('id', (int)$productId)->where('servertype', 'TSSGGSModule')->update(['hidden' => $hidden ? 1 : 0]);
    }

    /**
     * Retired products can not be ordered any more; existing services keep working
     */
    public static function setRetired($productId, $retired)
    {
        $columns = ['retired' => $retired ? 1 : 0];
        if($retired)
        {
            $columns['hidden'] = 1;
        }

        return Capsule::table('tblproducts')->where('id', (int)$productId)->where('servertype', 'TSSGGSModule')->update($columns);
    }

    /**
     * Differences between the product's SAN options and what the gateway product allows
     */
    public static function optionProblems($productId, array $remoteRaw)
    {
        $existing = self::sanOptions($productId);
        $problems = [];
        foreach(self::OPTIONS as $remote => list($name))
        {
            $allowed = !empty($remoteRaw['san'][$remote . '_allowed']);
            if($allowed && empty($existing[$name]))
            {
                $problems[] = $name . ' missing';
            }
            elseif(!empty($existing[$name]) && $existing[$name]->subs === 0)
            {
                $problems[] = $name . ' has no sub-option';
            }
            elseif(!empty($existing[$name]) && !$allowed && !$existing[$name]->hidden)
            {
                $problems[] = $name . ' not offered by the gateway';
            }
        }

        return $problems;
    }

    /**
     * Creates missing SAN options, adds the "SAN" sub-option, sets the quantity range from the gateway product and
     * hides options the gateway no longer offers. New options get the reseller cost as price (adjust on Pricing).
     */
    public static function fixConfigurableOptions($productId, array $remoteRaw)
    {
        $product = Capsule::table('tblproducts')->where('id', (int)$productId)->first();
        if(!$product)
        {
            throw new \RuntimeException('Product not found');
        }

        $existing = self::sanOptions($productId);
        $fixed    = [];
        foreach(self::OPTIONS as $remote => list($name, $label))
        {
            $allowed  = !empty($remoteRaw['san'][$remote . '_allowed']);
            $included = (int)($remote === 'single' ? $product->configoption7 : $product->configoption8);
            $max      = max(0, (int)($remoteRaw['san']['max'] ?? 0) - ($included - (int)($remoteRaw['san']['min'] ?? 0)));

            if(!$allowed)
            {
                if(!empty($existing[$name]) && !$existing[$name]->hidden)
                {
                    Capsule::table('tblproductconfigoptions')->where('id', $existing[$name]->id)->update(['hidden' => 1]);
                    $fixed[] = $name . ' hidden';
                }
                continue;
            }

            if(empty($existing[$name]))
            {
                $optionId = Capsule::table('tblproductconfigoptions')->insertGetId([
                    'gid' => self::configGroup($product), 'optionname' => $name . '|' . self::optionLabel($remote, $included), 'optiontype' => 4,
                    'qtyminimum' => 0, 'qtymaximum' => $max, 'order' => 0, 'hidden' => 0,
                ]);
                $fixed[] = $name . ' created';
            }
            else
            {
                $optionId = (int)$existing[$name]->id;
                $update   = ['optiontype' => 4, 'qtyminimum' => 0, 'qtymaximum' => $max];
                // the old default name ("Additional Single domain SAN's") gets the clearer one; names an admin wrote stay
                if(in_array(explode('|', (string)$existing[$name]->optionname, 2)[1] ?? '', self::OLD_LABELS, true))
                {
                    $update['optionname'] = $name . '|' . self::optionLabel($remote, $included);
                    $fixed[]              = $name . ' renamed';
                }
                Capsule::table('tblproductconfigoptions')->where('id', $optionId)->update($update);
            }

            $subId = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', $optionId)->value('id');
            if(!$subId)
            {
                $subId   = Capsule::table('tblproductconfigoptionssub')->insertGetId(['configid' => $optionId, 'optionname' => 'SAN', 'sortorder' => 0, 'hidden' => 0]);
                $fixed[] = $name . ' sub-option added';
            }
            else
            {
                Capsule::table('tblproductconfigoptionssub')->where('configid', $optionId)->update(['optionname' => 'SAN']);
            }

            self::ensurePricing($product, $subId, $remoteRaw, $remote);
        }

        return $fixed;
    }

    /**
     * Cancellation requests (end of billing period) for all active services of the product, so they do not renew
     */
    public static function cancelAllRenewals($productId, $reason)
    {
        $services = Capsule::table('tblhosting')->where('packageid', (int)$productId)->whereIn('domainstatus', ['Active', 'Suspended'])->pluck('id');
        $count    = 0;
        foreach($services as $serviceId)
        {
            if(Capsule::table('tblcancelrequests')->where('relid', $serviceId)->exists())
            {
                continue;
            }
            $result = localAPI('AddCancelRequest', ['serviceid' => $serviceId, 'type' => 'End of Billing Period', 'reason' => $reason]);
            if(($result['result'] ?? '') === 'success')
            {
                $count++;
            }
        }
        GeneralSettings::activity('Renewals cancelled for ' . $count . ' services of product #' . (int)$productId . ' (' . $reason . ')');

        return $count;
    }

    /**
     * Marks gateway products the last synchronization did not return (called by RemoteProduct::synchronize())
     */
    public static function markMissing($vendor, array $returnedIds)
    {
        $rows = Capsule::table('TSSGGSModule_RemoteProducts')->where('vendor', $vendor)->get(['id', 'remoteId', 'rawData']);
        foreach($rows as $row)
        {
            $raw = json_decode((string)$row->rawData, true) ?: [];
            if(in_array((string)$row->remoteId, $returnedIds, true) || !empty($raw['missing_since']))
            {
                continue;
            }
            $raw['missing_since'] = date('Y-m-d');
            Capsule::table('TSSGGSModule_RemoteProducts')->where('id', $row->id)->update(['rawData' => json_encode($raw)]);
        }
    }

    protected static function sanOptions($productId)
    {
        $rows = Capsule::table('tblproductconfiglinks')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
            ->where('tblproductconfiglinks.pid', (int)$productId)
            ->get(['tblproductconfigoptions.id', 'tblproductconfigoptions.optionname', 'tblproductconfigoptions.hidden']);

        $options = [];
        foreach($rows as $row)
        {
            $name = explode('|', (string)$row->optionname)[0];
            if(in_array($name, ['sans', 'sans_wildcard'], true) && !isset($options[$name]))
            {
                $row->subs      = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', $row->id)->count();
                $options[$name] = $row;
            }
        }

        return $options;
    }

    protected static function configGroup($product)
    {
        $gid = (int)Capsule::table('tblproductconfiglinks')->where('pid', $product->id)->value('gid');
        if($gid)
        {
            return $gid;
        }

        $gid = Capsule::table('tblproductconfiggroups')->insertGetId([
            'name' => 'Configurable options for ' . $product->name . ' product', 'description' => 'Auto generated by module ' . Edition::name(),
        ]);
        Capsule::table('tblproductconfiglinks')->insert(['gid' => $gid, 'pid' => $product->id]);

        return $gid;
    }

    /**
     * Pricing rows for every currency; a missing row gets the reseller cost (never below cost)
     */
    protected static function ensurePricing($product, $subId, array $remoteRaw, $remote)
    {
        $pricing = new PricingService();
        foreach(PricingService::currencies() as $currency)
        {
            if(Capsule::table('tblpricing')->where('type', 'configoptions')->where('relid', $subId)->where('currency', $currency->id)->exists())
            {
                continue;
            }
            $row = ['type' => 'configoptions', 'relid' => $subId, 'currency' => $currency->id, 'msetupfee' => 0, 'qsetupfee' => 0, 'ssetupfee' => 0,
                    'asetupfee' => 0, 'bsetupfee' => 0, 'tsetupfee' => 0, 'monthly' => 0, 'quarterly' => 0, 'semiannually' => 0, 'annually' => 0, 'biennially' => 0, 'triennially' => 0];
            foreach(PricingService::TERMS as $term => $column)
            {
                $cost = $pricing->remoteCost($remoteRaw, $remote === 'single' ? 'sans' : 'sans_wildcard', $term);
                if($cost !== null)
                {
                    $row[$column] = $pricing->convert($cost, $currency->id);
                }
            }
            if($product->paytype === 'onetime')
            {
                $row['monthly'] = $row['annually'];
            }
            Capsule::table('tblpricing')->insert($row);
        }
    }
}
