<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use WHMCS\Database\Capsule;

/**
 * Money rules of the subscription products (ACME and Plan + Automate), the same for both vendors:
 *  - a subscription cancelled within REFUND_DAYS of its order is refunded in full (the order and the domain slots bought
 *    since); a renewal is never refunded;
 *  - domain slots bought within the last REFUND_DAYS are refunded when the subscription is cancelled, and can be given
 *    back (downgrade) only within that window;
 *  - a domain removed within REFUND_DAYS of being added frees its slot (see the ACME domain list).
 */
class SubscriptionBilling
{
    const REFUND_DAYS = 30;

    /** credit description of a refund; the invoice number keeps a second cancellation from crediting twice */
    const CREDIT_PREFIX = 'Subscription refund for invoice #';
    /** the prefix of the credits written before 1.4 (ACME only) */
    const OLD_CREDIT_PREFIX = 'ACME refund for invoice #';

    /** the whole order is still refundable: the service was ordered at most REFUND_DAYS ago */
    public static function orderRefundable($serviceId)
    {
        $regdate = (string)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('regdate');

        return $regdate !== '' && $regdate !== '0000-00-00' && strtotime($regdate) >= strtotime('-' . self::REFUND_DAYS . ' days', strtotime(date('Y-m-d')));
    }

    /** date of the last paid purchase of domain slots (configurable option upgrade), else the order date */
    public static function lastSlotPurchase($serviceId)
    {
        $date = Capsule::table('tblupgrades')
            ->where('relid', (int)$serviceId)
            ->where('type', 'configoptions')
            ->where('paid', 'Y')
            ->max('date');

        return $date ?: (string)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('regdate');
    }

    /** bought domain slots can be given back only within REFUND_DAYS of their purchase */
    public static function canReduceSlots($serviceId)
    {
        $date = self::lastSlotPurchase($serviceId);

        return $date !== '' && $date !== '0000-00-00' && strtotime($date) >= strtotime('-' . self::REFUND_DAYS . ' days', strtotime(date('Y-m-d')));
    }

    /**
     * Credits the client for a cancellation: the order invoice when the order is within the refund window, and the
     * domain slot purchases of the last REFUND_DAYS. Renewal invoices are never refunded. Returns the credited amount.
     */
    public static function refundOnCancel($serviceId, $reason = '')
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::REFUND_DAYS . ' days'));
        $items = [];

        if(self::orderRefundable($serviceId))
        {
            $items = Capsule::table('tblinvoiceitems')
                ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
                ->where('tblinvoiceitems.type', 'Hosting')
                ->where('tblinvoiceitems.relid', (int)$serviceId)
                ->where('tblinvoiceitems.description', 'NOT LIKE', '%Renewal%')
                ->where('tblinvoices.status', 'Paid')
                ->where('tblinvoices.datepaid', '>', $since)
                ->get(['tblinvoiceitems.*'])
                ->all();
        }

        $upgradeIds = Capsule::table('tblupgrades')->where('relid', (int)$serviceId)->where('type', 'configoptions')->pluck('id')->all();
        if($upgradeIds)
        {
            $items = array_merge($items, Capsule::table('tblinvoiceitems')
                ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
                ->where('tblinvoiceitems.type', 'Upgrade')
                ->whereIn('tblinvoiceitems.relid', $upgradeIds)
                ->where('tblinvoices.status', 'Paid')
                ->where('tblinvoices.datepaid', '>', $since)
                ->get(['tblinvoiceitems.*'])
                ->all());
        }

        $byInvoice = [];
        foreach($items as $item)
        {
            $byInvoice[(int)$item->invoiceid][] = $item;
        }

        $credited = 0.0;
        foreach($byInvoice as $invoiceId => $invoiceItems)
        {
            $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
            if(!$invoice || self::alreadyCredited((int)$invoice->userid, $invoiceId) > 0)
            {
                continue;
            }
            $amount = 0.0;
            foreach($invoiceItems as $item)
            {
                $amount += self::amountWithTax($invoice, $item);
            }
            $amount = round($amount, 2);
            if($amount <= 0)
            {
                continue;
            }

            $result = localAPI('AddCredit', [
                'clientid'    => (int)$invoice->userid,
                'description' => self::CREDIT_PREFIX . $invoiceId . ($reason !== '' ? '. Reason: ' . $reason : ''),
                'amount'      => number_format($amount, 2, '.', ''),
            ]);
            if(($result['result'] ?? '') !== 'success')
            {
                \logActivity('TSSGGS: the refund credit for invoice #' . $invoiceId . ' (service #' . (int)$serviceId . ') could not be added: ' . ($result['message'] ?? 'unknown error'));
                continue;
            }
            \logActivity('TSSGGS: refund credit ' . number_format($amount, 2, '.', '') . ' added for invoice #' . $invoiceId . ' (service #' . (int)$serviceId . ').' . ($reason !== '' ? ' Reason: ' . $reason : ''));
            $credited += $amount;
        }

        return round($credited, 2);
    }

    protected static function alreadyCredited($clientId, $invoiceId)
    {
        return (float)Capsule::table('tblcredit')
            ->where('clientid', (int)$clientId)
            ->where(function($query) use ($invoiceId) {
                // the number is followed by the end of the text or by ". Reason: ..." (#12 must not match #120)
                foreach([self::CREDIT_PREFIX, self::OLD_CREDIT_PREFIX] as $prefix)
                {
                    $query->orWhere('description', $prefix . $invoiceId)->orWhere('description', 'LIKE', $prefix . $invoiceId . '. %');
                }
            })
            ->sum('amount');
    }

    /** the amount the client paid for an invoice item, tax included */
    protected static function amountWithTax($invoice, $item)
    {
        $amount = (float)$item->amount;
        if(!$item->taxed || !\WHMCS\Config\Setting::getValue('TaxEnabled'))
        {
            return $amount;
        }
        $client = function_exists('getClientsDetails') ? getClientsDetails((int)$invoice->userid) : [];
        if(!empty($client['taxexempt']))
        {
            return $amount;
        }
        // inclusive tax: the item amount already contains it
        if(\WHMCS\Config\Setting::getValue('TaxType') === 'Inclusive')
        {
            return $amount;
        }
        $rate1 = (float)$invoice->taxrate;
        $rate2 = (float)$invoice->taxrate2;
        $tax1  = $amount * $rate1 / 100;
        $tax2  = (\WHMCS\Config\Setting::getValue('TaxL2Compound') ? $amount + $tax1 : $amount) * $rate2 / 100;

        return $amount + $tax1 + $tax2;
    }

    /**
     * Price of one more year of the subscription in the client's currency: the product's one-time (yearly) price and,
     * for ACME, the domain slots the service has (configurable option quantity x price of a slot)
     */
    public static function renewalAmount($serviceId)
    {
        $service = Capsule::table('tblhosting')
            ->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')
            ->where('tblhosting.id', (int)$serviceId)
            ->first(['tblhosting.packageid', 'tblhosting.amount', 'tblhosting.billingcycle', 'tblclients.currency']);
        if(!$service)
        {
            return 0.0;
        }
        // a service on a recurring cycle keeps its own recurring amount; a one-time one has none (WHMCS puts the price
        // of the last slot upgrade there)
        if($service->billingcycle !== 'One Time')
        {
            return (float)$service->amount;
        }
        $currency = (int)$service->currency;
        $price    = function($type, $relid) use ($currency) {
            $row = Capsule::table('tblpricing')->where('type', $type)->where('relid', (int)$relid)->where('currency', $currency)->first(['monthly', 'annually']);
            // one-time products keep their price in "monthly"; -1 = not offered
            $value = $row ? (float)$row->monthly : 0.0;

            return $value > 0 ? $value : ($row && (float)$row->annually > 0 ? (float)$row->annually : 0.0);
        };

        $amount = $price('product', $service->packageid);
        foreach(Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->where('tblhostingconfigoptions.relid', (int)$serviceId)
            ->where('tblproductconfigoptions.optiontype', 4)
            ->get(['tblhostingconfigoptions.configid', 'tblhostingconfigoptions.qty']) as $option)
        {
            $sub = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', (int)$option->configid)->orderBy('sortorder')->value('id');
            if($sub && (int)$option->qty > 0)
            {
                $amount += (int)$option->qty * $price('configoptions', $sub);
            }
        }

        return round($amount, 2);
    }

    /** ACME or Plan + Automate product (billed as a one-time product with the module's own renewal invoice) */
    public static function isSubscriptionProduct($whmcsProductId)
    {
        return AcmeHelpers::isAcmeWhmcsProduct($whmcsProductId) || AisHelpers::isAisWhmcsProduct($whmcsProductId);
    }

    /** the gateway product of a subscription product */
    public static function isSubscriptionRemote($remoteProduct)
    {
        return $remoteProduct && in_array(strtolower((string)$remoteProduct->category), SSLTrustCenterApi::SUBSCRIPTION_CATEGORIES, true);
    }

    /**
     * ACME: the client bought more domain slots (configurable option upgrade, paid) - the same number is bought at the
     * vendor (gateway "add-san" of the subscription)
     */
    public static function buyAcmeSlots($serviceId, $single, $wildcard)
    {
        $single       = max(0, (int)$single);
        $wildcard     = max(0, (int)$wildcard);
        $subscription = AcmeSubscription::getByServiceId($serviceId);
        if((!$single && !$wildcard) || !$subscription || (int)$subscription->acme_id <= 0 || in_array($subscription->status, ['cancelled', 'terminated'], true))
        {
            return false;
        }
        CertificateService::api($serviceId)->addSan((int)$subscription->acme_id, ['single_san' => $single, 'wildcard_san' => $wildcard]);
        CertificateService::event($serviceId, 'success', '[Subscription] Additional domains bought: ' . $single . ' single, ' . $wildcard . ' wildcard');

        return true;
    }

    /** Settings > SSL > Subscriptions: domain slots bought during the period are charged for the days left ("prorated") or at the yearly price ("full") */
    const SLOT_PRICING_PRORATED = 'prorated';
    const SLOT_PRICING_FULL     = 'full';

    public static function slotPricing()
    {
        $settings = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['sslSettings'] ?? [];

        return ($settings['subscriptionSlotPricing'] ?? self::SLOT_PRICING_PRORATED) === self::SLOT_PRICING_FULL ? self::SLOT_PRICING_FULL : self::SLOT_PRICING_PRORATED;
    }

    /** yearly price of one slot of a quantity option (one-time products keep it in "monthly") in a currency */
    protected static function slotPrice($configId, $currencyId)
    {
        $sub = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', (int)$configId)->orderBy('sortorder')->value('id');
        $row = $sub ? Capsule::table('tblpricing')->where('type', 'configoptions')->where('relid', $sub)->where('currency', (int)$currencyId)->first(['monthly', 'annually']) : null;
        if(!$row)
        {
            return 0.0;
        }

        return (float)$row->monthly > 0 ? (float)$row->monthly : max(0.0, (float)$row->annually);
    }

    /** the subscription's next renewal date (ACME or Plan + Automate), '' when it is not known */
    protected static function renewalDate($serviceId)
    {
        $row = AcmeSubscription::getByServiceId($serviceId) ?: AisSubscription::getByServiceId($serviceId);

        return $row && $row->renewal_date ? date('Y-m-d', strtotime((string)$row->renewal_date)) : '';
    }

    /**
     * Prices the domain slots of an upgrade invoice (WHMCS charges a one-time product's option at its full price):
     *  - "prorated": slot price / 365 x days left until the next renewal (paid in advance for the next period when the
     *    renewal invoice was already paid, since the renewal date has then moved a year on);
     *  - "full": the yearly slot price.
     * Only the slots added by the upgrade are charged; giving slots back is not charged. Returns the items changed.
     */
    public static function priceSlotUpgradeInvoice($invoiceId)
    {
        $invoice = Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->first(['id', 'userid', 'status']);
        if(!$invoice || $invoice->status !== 'Unpaid')
        {
            return 0;
        }
        $currency = (int)Capsule::table('tblclients')->where('id', (int)$invoice->userid)->value('currency');
        $changed  = 0;
        $found    = 0;
        foreach(Capsule::table('tblinvoiceitems')->where('invoiceid', (int)$invoiceId)->where('type', 'Upgrade')->get() as $item)
        {
            $upgrade = Capsule::table('tblupgrades')->where('id', (int)$item->relid)->first();
            if(!$upgrade || $upgrade->type !== 'configoptions')
            {
                continue;
            }
            $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$upgrade->relid)->value('packageid');
            if(!AcmeHelpers::isAcmeWhmcsProduct($packageId))
            {
                continue;
            }
            $parts = explode('=>', (string)$upgrade->originalvalue);
            $added = count($parts) === 2 ? (int)$upgrade->newvalue - (int)$parts[1] : 0;
            if($added <= 0)
            {
                continue;
            }
            $found++;
            $amount = $added * self::slotPrice((int)$parts[0], $currency);
            if(self::slotPricing() === self::SLOT_PRICING_PRORATED)
            {
                $renewal = self::renewalDate((int)$upgrade->relid);
                $days    = $renewal !== '' ? (int)ceil((strtotime($renewal) - strtotime(date('Y-m-d'))) / 86400) : 365;
                $amount  = $amount / 365 * max(1, $days);
            }
            $amount = round($amount, 2);
            if(abs($amount - (float)$item->amount) < 0.005)
            {
                continue;
            }
            Capsule::table('tblinvoiceitems')->where('id', (int)$item->id)->update(['amount' => $amount]);
            Capsule::table('tblupgrades')->where('id', (int)$upgrade->id)->update(['amount' => $amount]);
            $changed++;
        }
        // the total also when nothing changed: WHMCS may write its own total after an earlier hook repriced the items
        if($found)
        {
            self::updateInvoiceTotal((int)$invoiceId);
        }
        if($changed)
        {
            \logActivity('TSSGGS: domain slots of upgrade invoice #' . (int)$invoiceId . ' priced ' . self::slotPricing() . '.');
        }

        return $changed;
    }

    /**
     * The unpaid renewal invoice follows the domain slots of the service until it is paid: the client pays for the
     * slots the subscription has on the last day before the renewal
     */
    public static function refreshRenewalInvoice($serviceId)
    {
        $item = Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblinvoiceitems.description', 'LIKE', '%' . AcmeHelpers::RENEWAL_LABEL)
            ->where('tblinvoices.status', 'Unpaid')
            ->orderBy('tblinvoices.id', 'desc')
            ->first(['tblinvoiceitems.id', 'tblinvoiceitems.amount', 'tblinvoiceitems.invoiceid']);
        if(!$item)
        {
            return false;
        }
        $amount = self::renewalAmount($serviceId);
        if(abs($amount - (float)$item->amount) < 0.005)
        {
            return false;
        }
        Capsule::table('tblinvoiceitems')->where('id', (int)$item->id)->update(['amount' => $amount]);
        self::updateInvoiceTotal((int)$item->invoiceid);
        CertificateService::event($serviceId, 'info', '[Subscription] Renewal invoice #' . (int)$item->invoiceid . ' updated to the current domain slots: ' . number_format($amount, 2, '.', ''));

        return true;
    }

    /**
     * A renewal invoice was paid: the service carries on with the same subscription (nothing is ordered again); its
     * renewal and due date move one year on. Done once per renewal (the invoice must belong to the coming renewal).
     */
    public static function renewalPaid($serviceId, $invoiceId)
    {
        $invoice = Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->first(['duedate']);
        foreach([AcmeSubscription::class, AisSubscription::class] as $model)
        {
            $subscription = $model::getByServiceId($serviceId);
            if(!$subscription || !$subscription->renewal_date || !$invoice)
            {
                continue;
            }
            $renewal = date('Y-m-d', strtotime((string)$subscription->renewal_date));
            // the invoice of this renewal is due the day before it (or on it, invoices before 1.4)
            if((string)$invoice->duedate < date('Y-m-d', strtotime('-1 day', strtotime($renewal))))
            {
                return false;
            }
            $next = date('Y-m-d', strtotime('+12 months', strtotime($renewal)));
            $subscription->renewal_date = $next;
            $subscription->period_end   = $next;
            $subscription->save();
            Capsule::table('tblhosting')->where('id', (int)$serviceId)->update(['nextduedate' => $next, 'nextinvoicedate' => $next]);
            CertificateService::event($serviceId, 'success', '[Subscription] Renewal invoice #' . (int)$invoiceId . ' paid: renewed until ' . $next);

            return true;
        }

        return false;
    }

    protected static function updateInvoiceTotal($invoiceId)
    {
        if(!function_exists('updateInvoiceTotal'))
        {
            require_once ROOTDIR . '/includes/invoicefunctions.php';
        }
        \updateInvoiceTotal((int)$invoiceId);
    }
}
