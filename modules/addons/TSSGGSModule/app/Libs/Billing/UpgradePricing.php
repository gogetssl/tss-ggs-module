<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Billing;

use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling;

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

    /** A certificate service of the module whose SAN upgrades are charged in full (Settings > SSL, default) */
    public static function eligibleService($serviceId)
    {
        return SanUpgrade::pricing() === SanUpgrade::PRICING_FULL && self::certificateService($serviceId);
    }

    /** An SSL/TLS certificate service of the module (not ACME, not a subscription) */
    public static function certificateService($serviceId)
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
        $full      = self::eligibleService($serviceId);
        // removed SANs are credited at the price paid (SanUpgrade::afterDowngrade) when WHMCS credits downgrades
        $refunds   = \WHMCS\Config\Setting::getValue('CreditOnDowngrade') === 'on' && self::certificateService($serviceId) && SanUpgrade::isOrderedCertificate($serviceId);
        if(!$full && !$refunds)
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

        // the code applies when WHMCS gave it a discount on its own prices (it checked dates, uses, client, options);
        // the discount is then worked out on the full prices, the same way as on the invoice
        $promo = !empty($vars['promocode']) && abs(self::numeric($vars['discount'] ?? 0)) >= 0.005 ? self::promotion($vars['promocode']) : null;

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
                    $difference = (float)$option['selectedrecurring'] - ($oldById[$configId] ?? 0);
                    if($full && in_array($configId, $sanIds, true) && $difference >= 0.005)
                    {
                        // the full price of the added SANs for the billing cycle
                        $price   = round($difference, 2);
                        $changed = true;
                        $upgrade['price'] = formatCurrency($price);
                    }
                    elseif($refunds && in_array($configId, $sanIds, true) && $difference <= -0.005)
                    {
                        // removed SANs: the price paid for them comes back as credit
                        $type   = explode('|', (string)Capsule::table('tblproductconfigoptions')->where('id', $configId)->value('optionname'))[0] === 'sans_wildcard' ? 'wildcard' : 'single';
                        $before = (int)Capsule::table('tblhostingconfigoptions')->where('relid', $serviceId)->where('configid', $configId)->value('qty');
                        $after  = (int)($vars['configoptions'][$configId] ?? $before);
                        $refund = $before > $after ? SanUpgrade::refundQuote($serviceId, [$type => $before - $after]) : null;
                        if($refund !== null)
                        {
                            $price   = -1 * $refund;
                            $changed = true;
                            $upgrade['price'] = formatCurrency($price);
                        }
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

        $net = round($subtotal - $discount, 2);
        // a credit (removed SANs) is shown without tax, as WHMCS credits it
        list($net, $tax, $tax2) = $net > 0 ? self::tax($net, $vars, self::taxable($service->packageid, $service->userid)) : [$net, 0.0, 0.0];

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

    /** Whether the upgrade is taxed: tax on in WHMCS, the product taxed, the client not tax exempt (as on the invoice) */
    protected static function taxable($packageId, $userId)
    {
        return \WHMCS\Config\Setting::getValue('TaxEnabled') === 'on'
            && (int)Capsule::table('tblproducts')->where('id', (int)$packageId)->value('tax')
            && !(int)Capsule::table('tblclients')->where('id', (int)$userId)->value('taxexempt');
    }

    /**
     * [net, tax, tax2] as WHMCS works them out (exclusive or inclusive, level 2 optionally compound); the rates are the
     * ones WHMCS gives the page for the client ($taxrate, $taxrate2)
     */
    protected static function tax($amount, array $vars, $taxable)
    {
        $tax = $tax2 = 0.0;
        if(!$taxable || (empty($vars['taxrate']) && empty($vars['taxrate2'])))
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

        // a formatted amount ("$1,234.56 USD", "1.234,56 €"): the last separator is the decimal one
        $text = preg_replace('/[^0-9.,\-]/', '', (string)$price);
        $dot  = strrpos($text, '.');
        $comma = strrpos($text, ',');
        if($dot !== false && $comma !== false)
        {
            $text = $dot > $comma ? str_replace(',', '', $text) : str_replace(',', '.', str_replace('.', '', $text));
        }
        elseif($comma !== false)
        {
            $text = preg_match('/,\d{1,2}$/', $text) ? str_replace(',', '.', $text) : str_replace(',', '', $text);
        }

        return (float)$text;
    }

    /* ---------------------------------------------------------------- invoice */

    /**
     * InvoiceCreation: the SAN lines of an upgrade invoice at full price and the promotion line worked out on that
     * price - while WHMCS still builds the invoice. WHMCS 9 refuses any change to the lines of a finalised invoice
     * ("only in Draft status"), and credit on the account can pay it the moment it is finalised, so this is the only
     * point where the price can be set. WHMCS recalculates the totals (and tax) after this hook. Runs once per invoice.
     */
    /**
     * A one-time service has no next due date: WHMCS still ends its upgrade invoice line with the period
     * "(22/11/2026 - 00/00/0000)". That period is removed (as the GoGetSSL module 2.x did). Any SSL service of the module.
     *
     * @return int invoice lines changed
     */
    public static function cleanOneTimePeriods($invoiceId)
    {
        $changed = 0;
        foreach(Capsule::table('tblinvoiceitems')->where('invoiceid', (int)$invoiceId)->where('type', 'Upgrade')->get(['id', 'relid', 'description']) as $item)
        {
            $serviceId = (int)Capsule::table('tblupgrades')->where('id', (int)$item->relid)->value('relid');
            $service   = $serviceId ? Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
                ->where('tblhosting.id', $serviceId)->first(['tblhosting.billingcycle', 'tblproducts.servertype']) : null;
            if(!$service || $service->servertype !== 'TSSGGSModule' || $service->billingcycle !== 'One Time')
            {
                continue;
            }
            // the period in any WHMCS date format, its end being the empty date
            $description = preg_replace('/\s*\([^()]*\s-\s(?:00[\/.\-]00[\/.\-]0000|0000-00-00)\)\s*$/', '', (string)$item->description);
            if($description !== null && $description !== (string)$item->description)
            {
                Capsule::table('tblinvoiceitems')->where('id', (int)$item->id)->update(['description' => $description]);
                $changed++;
            }
        }

        return $changed;
    }

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
        // totals and tax of the invoice from its lines (not loaded in every WHMCS context: admin area, API)
        if(!function_exists('updateInvoiceTotal') && defined('ROOTDIR') && is_file(ROOTDIR . '/includes/invoicefunctions.php'))
        {
            require_once ROOTDIR . '/includes/invoicefunctions.php';
        }
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
