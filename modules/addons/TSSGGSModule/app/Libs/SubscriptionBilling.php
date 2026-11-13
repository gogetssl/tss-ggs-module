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
        // domains bought on the module's invoice ("Buy more domains")
        $items = array_merge($items, Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', self::SLOT_PURCHASE)
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblinvoices.status', 'Paid')
            ->where('tblinvoices.datepaid', '>', $since)
            ->get(['tblinvoiceitems.*'])
            ->all());

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

    /** name of the Requests row that keeps a purchase of domains until its invoice is paid */
    const SLOT_PURCHASE = 'slotPurchase';

    /** a purchase of domains of the service waits for its payment */
    public static function pendingSlotPurchase($serviceId)
    {
        foreach(\ModulesGarden\TSSGGSModule\App\Models\Request::where('serviceid', (int)$serviceId)->where('name', self::SLOT_PURCHASE)->where('status', 'pending')->get() as $row)
        {
            $data   = json_decode((string)$row->request, true) ?: [];
            $status = (string)Capsule::table('tblinvoices')->where('id', (int)($data['invoice'] ?? 0))->value('status');
            if(in_array($status, ['Unpaid', 'Payment Pending', 'Draft'], true))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Price of domain slots bought now: the yearly slot price x number, for the days left until the next renewal
     * ("prorated", Settings > SSL) or the whole year ("full"). After the renewal was paid the renewal date has moved a
     * year on, so a prorated purchase then also covers the coming year.
     */
    public static function slotPurchaseAmount($serviceId, $configId, $count)
    {
        $currency = (int)Capsule::table('tblhosting')->join('tblclients', 'tblclients.id', '=', 'tblhosting.userid')->where('tblhosting.id', (int)$serviceId)->value('tblclients.currency');
        $amount   = max(0, (int)$count) * self::slotPrice((int)$configId, $currency);
        if(self::slotPricing() === self::SLOT_PRICING_PRORATED)
        {
            $renewal = self::renewalDate((int)$serviceId);
            $days    = $renewal !== '' ? (int)ceil((strtotime($renewal) - strtotime(date('Y-m-d'))) / 86400) : 365;
            $amount  = $amount / 365 * max(1, $days);
        }

        return round($amount, 2);
    }

    /**
     * Invoice for domain slots the client buys ([config option id => number]); the slots are added when it is paid
     * (applySlotPurchase). A purchase that costs nothing is added at once. Returns the invoice ID (0: added at once).
     */
    public static function createSlotPurchaseInvoice($serviceId, array $bought)
    {
        $service = Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', (int)$serviceId)->first(['tblhosting.userid', 'tblhosting.domain', 'tblhosting.paymentmethod', 'tblproducts.name', 'tblproducts.tax']);
        $renewal = self::renewalDate((int)$serviceId);
        $post    = ['userid' => (int)$service->userid, 'sendinvoice' => true, 'date' => date('Y-m-d'), 'duedate' => date('Y-m-d'), 'paymentmethod' => (string)$service->paymentmethod];
        $total   = 0.0;
        $n       = 0;
        foreach($bought as $configId => $count)
        {
            $name   = explode('|', (string)Capsule::table('tblproductconfigoptions')->where('id', (int)$configId)->value('optionname'));
            $amount = self::slotPurchaseAmount($serviceId, $configId, $count);
            $n++;
            $post['itemdescription' . $n] = $service->name . ($service->domain ? ' - ' . $service->domain : '') . ': ' . (int)$count . ' x ' . trim(end($name))
                . ($renewal !== '' ? ' (until ' . $renewal . ')' : '');
            $post['itemamount' . $n] = $amount;
            $post['itemtaxed' . $n]  = (int)$service->tax;
            $total += $amount;
        }

        if($total <= 0)
        {
            self::addSlots($serviceId, $bought);

            return 0;
        }
        $result = localAPI('CreateInvoice', $post);
        if(($result['result'] ?? '') !== 'success' || empty($result['invoiceid']))
        {
            throw new \Exception('The invoice could not be created: ' . ($result['message'] ?? 'unknown error'));
        }
        $invoiceId = (int)$result['invoiceid'];
        // the lines belong to the service (reports, refunds), not to a WHMCS upgrade order
        Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->update(['type' => self::SLOT_PURCHASE, 'relid' => (int)$serviceId]);
        \ModulesGarden\TSSGGSModule\App\Models\Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => self::SLOT_PURCHASE,
            'request'    => json_encode(['invoice' => $invoiceId, 'bought' => $bought, 'pricing' => self::slotPricing()]),
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        CertificateService::event($serviceId, 'info', '[Subscription] Invoice #' . $invoiceId . ' for additional domains: ' . number_format($total, 2, '.', '') . ' (' . self::slotPricing() . ')');

        return $invoiceId;
    }

    /** InvoicePaid: the domain slots of a paid purchase are added (once) */
    public static function applySlotPurchase($invoiceId)
    {
        $applied = 0;
        foreach(\ModulesGarden\TSSGGSModule\App\Models\Request::where('name', self::SLOT_PURCHASE)->where('status', 'pending')->get() as $row)
        {
            $data = json_decode((string)$row->request, true) ?: [];
            if((int)($data['invoice'] ?? 0) !== (int)$invoiceId)
            {
                continue;
            }
            \ModulesGarden\TSSGGSModule\App\Models\Request::where('id', (int)$row->id)->update(['status' => 'paid', 'updated_at' => date('Y-m-d H:i:s')]);
            self::addSlots((int)$row->serviceid, (array)($data['bought'] ?? []));
            $applied++;
        }

        return $applied;
    }

    /**
     * Adds bought domain slots to the service (configurable option quantity), buys them at the vendor, and brings an
     * unpaid renewal invoice up to date
     */
    protected static function addSlots($serviceId, array $bought)
    {
        $added = ['single' => 0, 'wildcard' => 0];
        foreach($bought as $configId => $count)
        {
            $exists = Capsule::table('tblhostingconfigoptions')->where('relid', (int)$serviceId)->where('configid', (int)$configId)->exists();
            if($exists)
            {
                Capsule::table('tblhostingconfigoptions')->where('relid', (int)$serviceId)->where('configid', (int)$configId)->increment('qty', (int)$count);
            }
            else
            {
                $sub = (int)Capsule::table('tblproductconfigoptionssub')->where('configid', (int)$configId)->orderBy('sortorder')->value('id');
                Capsule::table('tblhostingconfigoptions')->insert(['relid' => (int)$serviceId, 'configid' => (int)$configId, 'optionid' => $sub, 'qty' => (int)$count]);
            }
            $type = explode('|', (string)Capsule::table('tblproductconfigoptions')->where('id', (int)$configId)->value('optionname'))[0];
            $added[$type === 'sans_wildcard' ? 'wildcard' : 'single'] += (int)$count;
        }
        CertificateService::event($serviceId, 'success', '[Subscription] Domains added: ' . $added['single'] . ' single, ' . $added['wildcard'] . ' wildcard');
        self::refreshRenewalInvoice($serviceId);
        try
        {
            self::buyAcmeSlots($serviceId, $added['single'], $added['wildcard']);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', '[Subscription] The domains could not be bought at the vendor: ' . $e->getMessage());
        }
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
        // WHMCS does not let an unpaid invoice change: it is cancelled and a new one is issued for the current slots
        $service      = \ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service::find((int)$serviceId);
        $subscription = AcmeSubscription::getByServiceId($serviceId) ?: AisSubscription::getByServiceId($serviceId);
        if(!$service || !$subscription)
        {
            return false;
        }
        $cancel = localAPI('UpdateInvoice', ['invoiceid' => (int)$item->invoiceid, 'status' => 'Cancelled']);
        if(($cancel['result'] ?? '') !== 'success')
        {
            throw new \Exception('Renewal invoice #' . (int)$item->invoiceid . ' could not be replaced: ' . ($cancel['message'] ?? 'unknown error'));
        }
        AcmeHelpers::createAcmeSubscriptionRenewalInvoice($service, $service->product, $subscription);
        CertificateService::event($serviceId, 'info', '[Subscription] Renewal invoice #' . (int)$item->invoiceid . ' replaced for the current domain slots: ' . number_format($amount, 2, '.', ''));

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
