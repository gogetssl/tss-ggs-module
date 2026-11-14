<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * When a renewal is billed, and how it is placed. Everything counts from the END OF THE ORDER - for a certificate
 * the order the client paid for (one to three years, as the billing cycle), for an ACME / Plan + Automate
 * subscription the day the vendor renews it. The certificate's own expiry is never used: inside an order the
 * certificate is reissued as often as needed.
 *
 *   - the service falls due on the order end (WHMCS's next due date)
 *   - the renewal invoice is created N days before it (at most 30: a CA carries over no more than 30 days of the
 *     old certificate, so an earlier renewal would cost the client the difference)
 *   - the invoice falls due M days after it is created (the payment term)
 *
 * The module creates every renewal invoice of its services itself; WHMCS's own invoicing is kept away from them
 * (App\Hooks\RenewalInvoicing), so a client never gets two invoices for one renewal.
 *
 * A payment term longer than the lead puts the due date after the order end. That is allowed - it is the
 * reseller's call - but warning() says what it means.
 */
class RenewalPolicy
{
    const CERTIFICATE  = 'certificate';
    const SUBSCRIPTION = 'subscription';

    /** Longest lead: the days a CA carries over from the old certificate */
    const MAX_LEAD = 30;

    const DEFAULT_LEAD = 30;
    const DEFAULT_TERM = 14;

    const LEAD_OPTIONS = [1, 3, 7, 10, 14, 21, 30];
    const TERM_OPTIONS = [0, 1, 3, 7, 10, 14, 21, 30];

    /** sslSettings keys of each product type: [create invoices, lead, payment term] */
    const KEYS = [
        self::CERTIFICATE  => ['recurringCreateAutomaticRenewalInvoice', 'recurringDaysBeforeExpiry', 'recurringPaymentTerm'],
        self::SUBSCRIPTION => ['recurringCreateAutomaticRenewalInvoiceSubscription', 'recurringDaysBeforeExpirySubscription', 'recurringPaymentTermSubscription'],
    ];

    protected static $settings = null;

    protected static function settings(): array
    {
        if(self::$settings === null)
        {
            $config         = (new AddonModuleRepository())->getModuleConfiguration();
            self::$settings = isset($config['sslSettings']) && is_array($config['sslSettings']) ? $config['sslSettings'] : [];
        }

        return self::$settings;
    }

    /** Forget what was read, e.g. after the settings were saved */
    public static function reset(): void
    {
        self::$settings = null;
    }

    /**
     * Whether the module creates renewal invoices for this product type (on unless the administrator turned it off)
     */
    public static function invoicing(string $type): bool
    {
        $value = self::settings()[self::KEYS[$type][0]] ?? null;

        return $value === null || $value === '' ? true : !in_array(strtolower((string)$value), ['0', 'off', 'false', 'no'], true);
    }

    /**
     * Days before the order end on which the renewal invoice is created: 1 to 30
     */
    public static function lead(string $type): int
    {
        $value = self::settings()[self::KEYS[$type][1]] ?? null;
        $days  = is_numeric($value) ? (int)$value : self::DEFAULT_LEAD;

        return max(1, min(self::MAX_LEAD, $days));
    }

    /**
     * Days the client has to pay the renewal invoice
     */
    public static function term(string $type): int
    {
        $value = self::settings()[self::KEYS[$type][2]] ?? null;
        $days  = is_numeric($value) ? (int)$value : self::DEFAULT_TERM;

        return max(0, min(60, $days));
    }

    /**
     * Whether the renewal invoice of an order ending on $end (Y-m-d) is due today: from the lead until the end.
     * "On or after" the day, so a day without a cron run does not lose the invoice.
     */
    public static function invoiceDue(string $type, $end, $today = null): bool
    {
        $end   = substr((string)$end, 0, 10);
        $today = $today ?: date('Y-m-d');
        if($end === '' || $end === '0000-00-00')
        {
            return false;
        }

        return $today >= date('Y-m-d', strtotime('-' . self::lead($type) . ' days', strtotime($end))) && $today < $end;
    }

    /**
     * Due date of a renewal invoice created on $created: the payment term after it
     */
    public static function invoiceDueDate(string $type, $created = null): string
    {
        $created = $created ? substr((string)$created, 0, 10) : date('Y-m-d');

        return date('Y-m-d', strtotime('+' . self::term($type) . ' days', strtotime($created)));
    }

    /**
     * Earliest date an invoice of the renewal ending on $end can carry - used to tell this renewal's invoice from
     * one of an earlier period
     */
    public static function earliestInvoiceDate($end): string
    {
        return date('Y-m-d', strtotime('-' . (self::MAX_LEAD + 1) . ' days', strtotime(substr((string)$end, 0, 10))));
    }

    /**
     * What a lead and a payment term mean together. null when nothing is at risk; otherwise
     * ['level' => 'amber'|'red', 'days' => days after the end (0 = on it, -1 = the day before), 'key' => language key].
     *
     *   - certificate, due after the order end: a client who pays on the due date renews after the certificate
     *     expired. No cost to the reseller - the renewal is ordered only once paid - so amber.
     *   - subscription: an unpaid renewal is cancelled at the vendor once its invoice is overdue. Due on or after the
     *     renewal day, the vendor renews and charges the reseller before that - red. Due the day before, it is
     *     cancelled on the renewal day itself, racing the vendor - amber.
     */
    public static function warning(string $type, int $lead, int $term)
    {
        $after = $term - $lead;
        if($type === self::SUBSCRIPTION)
        {
            if($after >= 0)
            {
                return ['level' => 'red', 'days' => $after, 'key' => 'renewalDueAfterSubscriptionRenews'];
            }

            return $after === -1 ? ['level' => 'amber', 'days' => -1, 'key' => 'renewalDueDayBeforeRenewal'] : null;
        }

        return $after > 0 ? ['level' => 'amber', 'days' => $after, 'key' => 'renewalDueAfterCertificateEnds'] : null;
    }

    /**
     * Longest payment term with nothing at risk for a lead (what the warning suggests)
     */
    public static function safeTerm(string $type, int $lead): int
    {
        return max(0, $type === self::SUBSCRIPTION ? $lead - 2 : $lead);
    }

    /**
     * Whether a paid renewal is placed with the previous order's details (CSR, domains, contacts) at once.
     * One setting for every product type; until it is saved, an older install keeps what it had chosen for SSL.
     */
    public static function reuseDetails(): bool
    {
        $config  = (new AddonModuleRepository())->getModuleConfiguration();
        $general = isset($config['generalSettings']) && is_array($config['generalSettings']) ? $config['generalSettings'] : [];
        if(array_key_exists('renewReuseOrderDetails', $general))
        {
            return GeneralSettings::enabled('renewReuseOrderDetails');
        }
        $ssl = self::settings();
        if(array_key_exists('automaticProcessingOfRenewalOrders', $ssl) && $ssl['automaticProcessingOfRenewalOrders'] !== null)
        {
            return !in_array(strtolower((string)$ssl['automaticProcessingOfRenewalOrders']), ['', '0', 'off', 'false', 'no'], true);
        }

        return true;
    }

    /**
     * A paid renewal waits for the client's details: $serviceId (the renewing service itself, or the new service of
     * a renewal on a new order) will place the renewal of $priorServiceId's order once the client submits them.
     */
    public static function awaitDetails(int $serviceId, int $priorServiceId, string $priorItemId, string $priorEnd): void
    {
        \ModulesGarden\TSSGGSModule\App\Models\Request::updateOrInsert(
            ['serviceid' => $serviceId, 'name' => 'renewalPending'],
            [
                'request'    => json_encode(['prior_service' => $priorServiceId, 'prior_item' => $priorItemId, 'prior_end' => substr($priorEnd, 0, 19)]),
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * The renewal block of the order the client submits for a renewal that waits for details, null when the service
     * waits for nothing: the vendor then treats it as the renewal of the previous order and carries its days over.
     */
    public static function pendingRenewal(int $serviceId)
    {
        $value = \ModulesGarden\TSSGGSModule\App\Models\Request::where('serviceid', $serviceId)->where('name', 'renewalPending')->value('request');
        $data  = $value ? (json_decode((string)$value, true) ?: []) : [];
        if(empty($data['prior_item']))
        {
            return null;
        }

        // the days the previous certificate has left now, not when the renewal was paid
        $left = !empty($data['prior_end']) ? (int)floor((strtotime((string)$data['prior_end']) - time()) / 86400) : 0;

        return [
            'prior_order_id'       => (string)$data['prior_item'],
            'prior_certificate_id' => (string)$data['prior_item'],
            'remaining_days'       => CertificateService::renewalDays((int)($data['prior_service'] ?? $serviceId), $left > 0 ? $left + 1 : 0),
        ];
    }

    /** The renewal was ordered with the client's details */
    public static function renewalPlaced(int $serviceId): void
    {
        \ModulesGarden\TSSGGSModule\App\Models\Request::where('serviceid', $serviceId)->where('name', 'renewalPending')->delete();
    }

    /**
     * Whether a paid renewal keeps the same WHMCS service (true) or gets a new service of its own (false)
     */
    public static function sameService(): bool
    {
        $value = self::settings()['renewOrderViaExistingOrder'] ?? null;

        return $value === null ? true : !in_array(strtolower((string)$value), ['', '0', 'off', 'false', 'no'], true);
    }
}
