<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use WHMCS\Database\Capsule;

/**
 * Renewal prices of existing services (Settings > Finance > Renewal prices of existing services).
 * WHMCS keeps the recurring amount a service was ordered with, so when the price list changes the renewals are
 * still billed at the old price - below cost when the reseller cost went up. After every price update:
 *   never      the recurring amounts are left alone (WHMCS default)
 *   belowCost  a service whose recurring amount is below its current reseller cost gets the current price
 *   always     every service follows the current price (up and down)
 * The current price is what WHMCS would charge today (product + SAN options + promotion, the same as the
 * "Recalculate on Save" of the service page) with the client's commission rule applied.
 */
class RenewalPrices
{
    const CYCLE_MONTHS = ['annually' => 12, 'biennially' => 24, 'triennially' => 36];
    const SKIP_CYCLES  = ['one time', 'onetime', 'free account', 'free'];

    public static function sync(): array
    {
        $stats = ['checked' => 0, 'updated' => 0, 'raised' => 0, 'lowered' => 0];
        $mode  = FinancePolicy::renewalMode();
        if($mode === 'never')
        {
            return $stats;
        }

        foreach(['orderfunctions', 'invoicefunctions', 'configoptionsfunctions'] as $file)
        {
            if(defined('ROOTDIR') && is_file(ROOTDIR . '/includes/' . $file . '.php'))
            {
                require_once ROOTDIR . '/includes/' . $file . '.php';
            }
        }
        if(!function_exists('recalcRecurringProductPrice'))
        {
            Logger::error('Renewal prices: WHMCS function recalcRecurringProductPrice is not available, nothing was updated');

            return $stats;
        }

        $pricing  = new PricingService();
        $products = $pricing->products();
        $services = Capsule::table('tblhosting as h')
            ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->join('tblclients as c', 'c.id', '=', 'h.userid')
            ->where('p.servertype', 'TSSGGSModule')
            ->whereIn('h.domainstatus', ['Active', 'Suspended'])
            ->get(['h.id', 'h.userid', 'h.packageid', 'h.billingcycle', 'h.amount', 'c.currency']);

        foreach($services as $service)
        {
            if(in_array(strtolower((string)$service->billingcycle), self::SKIP_CYCLES, true) || !isset($products[(int)$service->packageid]))
            {
                continue;
            }
            $stats['checked']++;

            $old = round((float)$service->amount, 2);
            $new = self::currentPrice($service);
            if($new === null || $new <= 0 || abs($new - $old) < 0.005)
            {
                continue;
            }

            if($mode === 'belowCost')
            {
                $cost = self::cost($pricing, $products[(int)$service->packageid], $service);
                if($cost === null || $old >= $cost || $new <= $old)
                {
                    continue;
                }
            }

            Capsule::table('tblhosting')->where('id', (int)$service->id)->update(['amount' => $new]);
            $stats['updated']++;
            $stats[$new > $old ? 'raised' : 'lowered']++;

            $text = 'Renewal price of service #' . (int)$service->id . ' changed from ' . number_format($old, 2) . ' to ' . number_format($new, 2) . ' (current price list)';
            Logger::info($text, ['service' => (int)$service->id, 'from' => $old, 'to' => $new]);
            GeneralSettings::activity($text, (int)$service->userid);

            if($new > $old && GeneralSettings::enabled('renewalPriceClientEmail'))
            {
                self::emailClient($service, $old, $new);
            }
        }

        return $stats;
    }

    /** What WHMCS would charge for the service today, with the client's commission rule */
    protected static function currentPrice($service)
    {
        $price = (float)\recalcRecurringProductPrice((int)$service->id);

        $commission = Commissions::forProduct((int)$service->userid, (int)$service->packageid);
        if($commission !== null)
        {
            $column = strtolower(str_replace(['-', ' '], '', (string)$service->billingcycle));
            $base   = Capsule::table('tblpricing')->where('type', 'product')->where('relid', (int)$service->packageid)->where('currency', (int)$service->currency)->value($column);
            if($base !== null && (float)$base > 0)
            {
                $price += Commissions::apply($base, $commission) - (float)$base; // the commission is on the certificate, not the SAN options
            }
        }

        return round($price, 2);
    }

    /** Reseller cost of the service for its billing cycle in the client's currency, null when unknown */
    protected static function cost(PricingService $pricing, array $product, $service)
    {
        $months = self::CYCLE_MONTHS[strtolower((string)$service->billingcycle)] ?? null;
        if(!$months && $product['paytype'] === 'onetime')
        {
            $months = 12;
        }
        if(!$months)
        {
            return null;
        }

        $base = $pricing->remoteCost($product['remote'], 'base', $months);
        if($base === null)
        {
            return null;
        }
        $cost = $pricing->convert($base, (int)$service->currency);

        // SANs bought on top of the ones included in the product
        $included = Capsule::table('tblproducts')->where('id', (int)$service->packageid)->first(['configoption7', 'configoption8']);
        $current  = SanUpgrade::currentQuantities((int)$service->id);
        foreach(['single' => ['sans', 'configoption7'], 'wildcard' => ['sans_wildcard', 'configoption8']] as $type => list($item, $column))
        {
            $bought = max(0, (int)($current[$type] ?? 0) - (int)($included->$column ?? 0));
            $unit   = $bought ? $pricing->remoteCost($product['remote'], $item, $months) : null;
            if($unit !== null)
            {
                $cost += $bought * $pricing->convert($unit, (int)$service->currency);
            }
        }

        return round($cost, 2);
    }

    protected static function emailClient($service, $old, $new)
    {
        $text = trim((string)GeneralSettings::get('renewalPriceEmailText')) ?: self::defaultEmailText();

        $result = localAPI('SendEmail', [
            'id'             => (int)$service->id,
            'customtype'     => 'product',
            'customsubject'  => 'Renewal price of {$service_product_name} ({$service_domain})',
            'custommessage'  => nl2br(htmlspecialchars($text, ENT_NOQUOTES)),
            'customvars'     => base64_encode(serialize(['old_price' => formatCurrency($old, (int)$service->currency), 'new_price' => formatCurrency($new, (int)$service->currency)])),
        ]);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Renewal price e-mail for service #' . (int)$service->id . ' failed: ' . ($result['message'] ?? 'unknown error'), (int)$service->userid);
        }
    }

    public static function defaultEmailText(): string
    {
        return "Dear {\$client_first_name},\n\nThe renewal price of {\$service_product_name} for {\$service_domain} changes from {\$old_price} to {\$new_price}, "
            . "because the price of the certificate authority has changed.\n\nThe new price applies from your next renewal on {\$service_next_due_date}.\n\n{\$signature}";
    }
}
