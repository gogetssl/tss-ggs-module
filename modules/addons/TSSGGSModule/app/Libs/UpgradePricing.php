<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Additional SANs bought after the order (WHMCS "Upgrade/Downgrade Options") cost their full price for the billing
 * cycle, not WHMCS's prorated price: the vendor charges the reseller the full SAN price whenever it is added.
 * Promotion codes are applied to that full price.
 *
 * WHMCS computes upgrade prices in encoded code, so two hooks correct them (as the GoGetSSL module 2.x did):
 * - ClientAreaPageUpgrade (summary step): the prices, discount, tax and total the client confirms
 * - InvoiceCreation (the invoice is still being built): the upgrade invoice and its promotion line;
 *   InvoiceCreated: the order amount follows
 *
 * Only SSL/TLS certificate products of the module and only their SAN options ("sans", "sans_wildcard"). ACME and
 * Plan + Automate domain slots have their own setting (Settings > SSL > Subscriptions); other configurable options
 * keep WHMCS's price.
 */
class UpgradePricing
{
    const SAN_OPTIONS = ['sans', 'sans_wildcard'];

    /** Invoices already corrected (one tblRequests row each) */
    const DONE = 'upgradeFullPrice';

    /** A certificate service of the module whose SAN upgrades are charged in full */
    public static function eligibleService($serviceId)
    {
        $row = Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', (int)$serviceId)->first(['tblproducts.id', 'tblproducts.servertype', 'tblproducts.configoption9']);
        if(!$row || $row->servertype !== 'TSSGGSModule' || strtolower((string)$row->configoption9) !== 'tls')
        {
            return false;
        }

        return !SubscriptionBilling::isSubscriptionProduct((int)$row->id) && !AcmeHelpers::isAcmeWhmcsProduct((int)$row->id);
    }

    /** IDs of the SAN configurable options of a product */
    public static function sanOptionIds($packageId)
    {
        $ids = [];
        $rows = Capsule::table('tblproductconfiglinks')->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
            ->where('tblproductconfiglinks.pid', (int)$packageId)->get(['tblproductconfigoptions.id', 'tblproductconfigoptions.optionname']);
        foreach($rows as $row)
        {
            if(in_array(explode('|', (string)$row->optionname)[0], self::SAN_OPTIONS, true))
            {
                $ids[] = (int)$row->id;
            }
        }

        return $ids;
    }

    /**
     * Discount of a WHMCS upgrade promotion on one item: ['discounttype' => Percentage|Fixed Amount, 'value',
     * 'configoptions' => [ids] (empty = all)]
     */
    public static function itemDiscount(array $promo, $configId, $price)
    {
        $options = array_map('intval', (array)($promo['configoptions'] ?? []));
        if($price <= 0 || ($options && !in_array((int)$configId, $options, true)))
        {
            return 0.0;
        }
        $value = (float)($promo['value'] ?? 0);

        return ($promo['discounttype'] ?? '') === 'Percentage' ? round($price * $value / 100, 2) : min($price, $value);
    }

    /** The upgrade settings of a promotion code (tblpromotions.upgradeconfig), or null when it has none */
    public static function promotion($code)
    {
        $code = trim((string)$code);
        if($code === '')
        {
            return null;
        }
        $row = Capsule::table('tblpromotions')->where('code', $code)->first(['upgrades', 'upgradeconfig']);
        if(!$row || !(int)$row->upgrades)
        {
            return null;
        }
        $config = @unserialize((string)$row->upgradeconfig, ['allowed_classes' => false]);
        if(!is_array($config))
        {
            $config = json_decode((string)$row->upgradeconfig, true);
        }

        return is_array($config) && isset($config['value']) ? $config : null;
    }

    /* ---------------------------------------------------------------- upgrade page */

    /**
     * ClientAreaPageUpgrade, summary step of a configurable options upgrade: the values the template shows, or [] to
     * leave WHMCS's page as it is
     */
    public static function summary(array $vars)
    {
        if(($vars['type'] ?? '') !== 'configoptions' || empty($vars['upgrades']) || !function_exists('getCartConfigOptions'))
        {
            return [];
        }
        $serviceId = (int)($vars['id'] ?? 0);
        if(!self::eligibleService($serviceId))
        {
            return [];
        }
        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first(['packageid', 'billingcycle', 'userid']);
        if(!$service || (int)$service->userid !== (int)($vars['clientsdetails']['userid'] ?? $service->userid))
        {
            return [];
        }
        $sanIds = self::sanOptionIds($service->packageid);
        if(!$sanIds)
        {
            return [];
        }

        $new = getCartConfigOptions($service->packageid, (array)($vars['configoptions'] ?? []), $service->billingcycle);
        $old = getCartConfigOptions($service->packageid, '', $service->billingcycle, $serviceId);
        $oldById = [];
        foreach((array)$old as $option)
        {
            $oldById[(int)$option['id']] = (float)($option['selectedrecurring'] ?? 0);
        }

        $promo = null;
        if(!empty($vars['promocode']) && function_exists('validateUpgradePromo'))
        {
            $data  = validateUpgradePromo($vars['promocode']);
            $promo = is_array($data) && !empty($GLOBALS['qualifies']) ? $data : null;
        }

        $changed  = false;
        $subtotal = 0.0;
        $discount = 0.0;
        $upgrades = [];
        foreach((array)$vars['upgrades'] as $upgrade)
        {
            $price    = self::numeric($upgrade['price'] ?? 0);
            $configId = null;
            foreach((array)$new as $option)
            {
                if((string)$option['optionname'] === (string)($upgrade['configname'] ?? ''))
                {
                    $configId = (int)$option['id'];
                    if(in_array($configId, $sanIds, true))
                    {
                        // the full price of the added SANs for the billing cycle
                        $price   = round(max(0, (float)$option['selectedrecurring'] - ($oldById[$configId] ?? 0)), 2);
                        $changed = true;
                        $upgrade['price'] = formatCurrency($price);
                    }
                    break;
                }
            }
            $subtotal += $price;
            if($promo)
            {
                $discount += self::itemDiscount($promo, $configId, $price);
            }
            $upgrades[] = $upgrade;
        }
        if(!$changed)
        {
            return [];
        }

        $net = max(0, round($subtotal - $discount, 2));
        list($net, $tax, $tax2) = self::tax($net, $vars);

        return [
            'upgrades' => $upgrades,
            // before the discount, as WHMCS shows it: subtotal, discount, then the total
            'subtotal' => formatCurrency($subtotal),
            'discount' => formatCurrency($discount),
            'tax'      => formatCurrency($tax),
            'tax2'     => formatCurrency($tax2),
            'total'    => formatCurrency($net + $tax + $tax2),
        ];
    }

    /** [net, tax, tax2] as WHMCS works them out (exclusive or inclusive, level 2 optionally compound) */
    protected static function tax($amount, array $vars)
    {
        $tax = $tax2 = 0.0;
        if(empty($vars['taxenabled']))
        {
            return [$amount, 0.0, 0.0];
        }
        $inclusive = \WHMCS\Config\Setting::getValue('TaxType') === 'Inclusive';
        $rate      = (float)($vars['taxrate'] ?? 0);
        $rate2     = (float)($vars['taxrate2'] ?? 0);
        $compound  = (bool)\WHMCS\Config\Setting::getValue('TaxL2Compound');
        if($inclusive)
        {
            // the amount already holds the tax: split it out
            $gross = $amount;
            $net   = $compound ? $gross / ((1 + $rate / 100) * (1 + $rate2 / 100)) : $gross / (1 + ($rate + $rate2) / 100);
            $tax  = round($net * $rate / 100, 2);
            $tax2 = round(($compound ? $net + $tax : $net) * $rate2 / 100, 2);

            return [round($gross - $tax - $tax2, 2), $tax, $tax2];
        }
        $tax  = round($amount * $rate / 100, 2);
        $tax2 = $rate2 ? round(($compound ? $amount + $tax : $amount) * $rate2 / 100, 2) : 0.0;

        return [$amount, $tax, $tax2];
    }

    protected static function numeric($price)
    {
        if(is_object($price) && method_exists($price, 'toNumeric'))
        {
            return (float)$price->toNumeric();
        }

        return (float)preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', (string)$price));
    }

    /* ---------------------------------------------------------------- invoice */

    /**
     * InvoiceCreation: the SAN lines of an upgrade invoice at full price and the promotion line worked out on that
     * price - while WHMCS still builds the invoice. WHMCS 9 refuses any change to the lines of a finalised invoice
     * ("only in Draft status"), and credit on the account can pay it the moment it is finalised, so this is the only
     * point where the price can be set. WHMCS recalculates the totals (and tax) after this hook. Runs once per invoice.
     */
    public static function fixInvoice($invoiceId)
    {
        $invoiceId = (int)$invoiceId;
        if(!$invoiceId || \ModulesGarden\TSSGGSModule\App\Models\Request::where('name', self::DONE)->where('serviceid', $invoiceId)->exists())
        {
            return false;
        }
        $items = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->get(['id', 'type', 'relid', 'description', 'amount', 'taxed']);
        $lines     = [];   // invoice item id => [new amount, config option id, WHMCS's amount, upgrade id]
        $orderId   = 0;
        $serviceId = 0;
        foreach($items as $item)
        {
            if($item->type !== 'Upgrade')
            {
                continue;
            }
            $upgrade = Capsule::table('tblupgrades')->where('id', (int)$item->relid)->first();
            if(!$upgrade || $upgrade->type !== 'configoptions' || !self::eligibleService($upgrade->relid))
            {
                continue;
            }
            $configId  = (int)explode('=>', (string)$upgrade->originalvalue)[0];
            $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$upgrade->relid)->value('packageid');
            $amount    = (float)$item->amount;
            if(in_array($configId, self::sanOptionIds($packageId), true) && (float)$upgrade->recurringchange > 0)
            {
                // the full price of the added SANs for the billing cycle
                $amount = round((float)$upgrade->recurringchange, 2);
            }
            $lines[(int)$item->id] = [$amount, $configId, (float)$item->amount, (int)$upgrade->id];
            $orderId   = (int)$upgrade->orderid;
            $serviceId = (int)$upgrade->relid;
        }
        $changed = array_filter($lines, function($line) { return abs($line[0] - $line[2]) >= 0.005; });
        if(!$changed)
        {
            return false;
        }

        $set = []; // invoice item id => amount
        foreach($changed as $itemId => $line)
        {
            $set[$itemId] = $line[0];
        }
        // the promotion line (negative, naming the code) is worked out again on the corrected prices
        $code  = $orderId ? (string)Capsule::table('tblorders')->where('id', $orderId)->value('promocode') : '';
        $promo = self::promotion($code);
        if($promo)
        {
            $discount = 0.0;
            foreach($lines as $line)
            {
                $discount += self::itemDiscount($promo, $line[1], $line[0]);
            }
            foreach($items as $item)
            {
                if((float)$item->amount < 0 && $item->type !== 'Upgrade' && stripos((string)$item->description, $code) !== false)
                {
                    $set[(int)$item->id] = -1 * round($discount, 2);
                    break;
                }
            }
        }

        Capsule::connection()->transaction(function() use ($set, $changed) {
            foreach($set as $itemId => $amount)
            {
                Capsule::table('tblinvoiceitems')->where('id', (int)$itemId)->update(['amount' => $amount]);
            }
            foreach($changed as $line)
            {
                Capsule::table('tblupgrades')->where('id', $line[3])->update(['amount' => $line[0]]);
            }
        });
        if(function_exists('updateInvoiceTotal'))
        {
            updateInvoiceTotal($invoiceId);
        }
        \ModulesGarden\TSSGGSModule\App\Models\Request::insert([
            'serviceid' => $invoiceId, 'name' => self::DONE, 'request' => json_encode(['service' => $serviceId, 'order' => $orderId, 'lines' => $changed, 'set' => $set]),
            'status' => 'done', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if($serviceId)
        {
            CertificateService::event($serviceId, 'info', '[Upgrade] Invoice #' . $invoiceId . ': additional SANs charged at full price');
        }

        return true;
    }

    /** InvoiceCreated: the upgrade order's amount follows the corrected invoice */
    public static function syncOrder($invoiceId)
    {
        $row = \ModulesGarden\TSSGGSModule\App\Models\Request::where('name', self::DONE)->where('serviceid', (int)$invoiceId)->first();
        $data = $row ? (json_decode((string)$row->request, true) ?: []) : [];
        if(empty($data['order']))
        {
            return;
        }
        $total = (float)Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->value('total');
        Capsule::table('tblorders')->where('id', (int)$data['order'])->update(['amount' => $total]);
    }
}
