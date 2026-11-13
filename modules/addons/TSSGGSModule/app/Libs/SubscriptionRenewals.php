<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Service;
use WHMCS\Database\Capsule;

/**
 * Renewal of the subscription products (ACME and Plan + Automate), the same for both vendors. The vendor renews a
 * subscription by itself on its renewal date and charges the reseller; the module bills the client:
 *  - the renewal invoice is created "Days Before Expiry" before the renewal date and is due the day before it;
 *  - an invoice still unpaid after its due date: the subscription is cancelled at the vendor (it ends with the paid
 *    period, no refund) and the service terminated (setting "Terminate Unpaid Subscriptions");
 *  - auto-renewal turned off and the period over: the service is cancelled;
 *  - after the renewal date the new period is read from the gateway.
 * Services billed by WHMCS itself (a recurring billing cycle, Plan + Automate ordered before 1.4) get no invoice from
 * here: WHMCS invoices them and terminates them (which cancels the subscription at the vendor).
 */
class SubscriptionRenewals
{
    /** gateway category => [subscription model, item id column] */
    const TYPES = [
        'caas' => [AcmeSubscription::class, 'acme_id'],
        'ais'  => [AisSubscription::class, 'ais_item_id'],
    ];

    const CLOSED = ['cancelled', 'terminated', 'expired', 'refunded', 'revoked'];

    /** Returns the errors of the run ([service id => message]) */
    public static function run(array $sslSettings)
    {
        $errors = [];
        AisHelpers::ensureColumns();
        foreach(self::TYPES as $category => [$model, $itemColumn])
        {
            foreach($model::query()->get() as $subscription)
            {
                if(in_array(strtolower((string)$subscription->status), self::CLOSED, true) || (int)$subscription->{$itemColumn} <= 0)
                {
                    continue;
                }
                try
                {
                    self::handle($category, $subscription, $itemColumn, $sslSettings);
                }
                catch(\Throwable $e)
                {
                    // one subscription must not stop the others
                    $errors[(int)$subscription->service_id] = $e->getMessage();
                    CertificateService::event((int)$subscription->service_id, 'error', '[Subscription] Renewal handling failed: ' . $e->getMessage());
                }
            }
        }

        return $errors;
    }

    protected static function handle($category, $subscription, $itemColumn, array $settings)
    {
        $serviceId = (int)$subscription->service_id;
        $service   = Service::find($serviceId);
        if(!$service || in_array($service->domainstatus, ['Cancelled', 'Terminated', 'Fraud'], true))
        {
            return;
        }
        $today       = date('Y-m-d');
        $billedHere  = $service->billingcycle === 'One Time';
        $renewalDate = $subscription->renewal_date ? date('Y-m-d', strtotime((string)$subscription->renewal_date)) : '';

        // the renewal date has come: the vendor renewed (or ended) the subscription - read the new period
        if($renewalDate !== '' && $renewalDate <= $today)
        {
            self::refresh($category, $serviceId);
            $subscription = $subscription->fresh();
            $renewalDate  = $subscription->renewal_date ? date('Y-m-d', strtotime((string)$subscription->renewal_date)) : '';
            if(in_array(strtolower((string)$subscription->status), self::CLOSED, true))
            {
                return;
            }
        }

        // auto-renewal off and the paid period is over: the subscription ends
        $end = $renewalDate !== '' ? $renewalDate : ($subscription->period_end ? date('Y-m-d', strtotime((string)$subscription->period_end)) : '');
        if(!$subscription->auto_renew)
        {
            if($end !== '' && $end <= $today)
            {
                self::close($category, $subscription, $itemColumn, 'Cancelled', 'Auto-renewal turned off and the subscription period ended');
            }

            return;
        }

        if(!$billedHere)
        {
            return;
        }

        // an unpaid renewal invoice past its due date
        if(!self::settingOff($settings, 'terminateUnpaidSubscriptions') && self::overdueRenewalInvoice($serviceId))
        {
            self::close($category, $subscription, $itemColumn, 'Terminated', 'Renewal invoice not paid');

            return;
        }

        // the renewal invoice, from "Days Before Expiry" until the renewal date (a day without a cron run loses nothing)
        if(!empty($settings['recurringCreateAutomaticRenewalInvoiceSubscription']) && $renewalDate !== '' && $renewalDate > $today)
        {
            $invoiceDate = AcmeHelpers::getAcmeNextInvoiceDate($renewalDate, max(1, (int)($settings['recurringDaysBeforeExpirySubscription'] ?? 30)));
            if($invoiceDate !== null && $invoiceDate <= $today
                && AcmeHelpers::createAcmeSubscriptionRenewalInvoice($service, $service->product, $subscription)
                && !empty($settings['recurringSendExpirationNotificationsSubscription']))
            {
                EmailTemplates::sendEmail(EmailTemplates::SUBSCRIPTION_EXPIRATION_TEMPLATE, $serviceId, []);
            }
        }
    }

    protected static function refresh($category, $serviceId)
    {
        $category === 'caas' ? AcmeHelpers::refreshAcmeCertificateDetails($serviceId) : AisHelpers::refreshAisDetails($serviceId);
    }

    /** an unpaid "Subscription Renewal" invoice of the service whose due date has passed */
    protected static function overdueRenewalInvoice($serviceId)
    {
        return Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblinvoiceitems.relid', (int)$serviceId)
            ->where('tblinvoiceitems.description', 'LIKE', '%' . AcmeHelpers::RENEWAL_LABEL)
            ->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending'])
            ->where('tblinvoices.duedate', '<', date('Y-m-d'))
            ->exists();
    }

    /**
     * Cancels the subscription at the vendor (no refund: it ends with the paid period) and closes the service. When the
     * vendor can not be reached the service stays open and it is tried again on the next run; after 14 days the
     * administrator is told to cancel it by hand.
     */
    protected static function close($category, $subscription, $itemColumn, $serviceStatus, $reason)
    {
        $serviceId = (int)$subscription->service_id;
        try
        {
            CertificateService::api($serviceId)->cancelItem((int)$subscription->{$itemColumn}, $reason, $category, false);
        }
        catch(\Throwable $e)
        {
            $since = $subscription->renewal_date ?: $subscription->period_end;
            if(!$since || date('Y-m-d', strtotime((string)$since)) >= date('Y-m-d', strtotime('-14 days')))
            {
                throw $e;
            }
            GeneralSettings::activity('Subscription of service #' . $serviceId . ' could not be cancelled at the vendor for 14 days - cancel it manually: ' . $e->getMessage());
        }

        $subscription->status       = 'cancelled';
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->auto_renew   = 0;
        $subscription->save();
        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => $serviceStatus] + ($serviceStatus === 'Terminated' ? ['termination_date' => date('Y-m-d')] : []));
        CertificateService::event($serviceId, 'info', '[Subscription] ' . $reason . ': cancelled at the vendor, service ' . strtolower($serviceStatus));
    }

    protected static function settingOff(array $settings, $key)
    {
        return isset($settings[$key]) && in_array(strtolower((string)$settings[$key]), ['', '0', 'off', 'false'], true);
    }
}
