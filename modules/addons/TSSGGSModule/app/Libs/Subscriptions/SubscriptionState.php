<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Subscriptions;

use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;


/**
 * State of an ACME / Plan + Automate subscription as the gateway documents it (API v2, 2026-11-19), the same for both
 * vendors:
 * - the EAB keys are returned before the ACME account is ready; the ACME server accepts them only once
 *   account.status is "active";
 * - a renewal the vendor could not charge leaves the subscription without a period: the "subscription" block disappears
 *   from the details and order.status is "pending" until it is paid (no new certificates, issued ones stay valid);
 * - once auto-renewal is stopped the subscription runs to its end date, and a cancellation is refused (GGS-3).
 */
class SubscriptionState
{
    const CLOSED = ['cancelled', 'terminated', 'expired'];


    /** items[0].account.status of a details answer, lower case ('' when not given) */
    public static function accountStatus(array $item)
    {
        $account = isset($item['account']) && is_array($item['account']) ? $item['account'] : [];

        return strtolower(trim((string)($account['status'] ?? '')));
    }

    /** The EAB keys can be given to an ACME client: the account is active (or its status is not known yet) */
    public static function keysUsable($subscription)
    {
        $status = $subscription ? strtolower(trim((string)$subscription->account_status)) : '';

        return $status === '' || $status === 'active';
    }

    /**
     * The renewal could not be charged: the period is over, the gateway gives no new one and the order is pending
     * (pending before the first period is an order not set up yet - not this).
     */
    public static function renewalUnpaid($subscription)
    {
        if(!$subscription || (int)$subscription->api_order_id <= 0 || strtolower((string)$subscription->status) !== 'pending')
        {
            return false;
        }
        $end = self::periodEnd($subscription);

        return $end !== '' && $end < date('Y-m-d');
    }

    /**
     * Auto-renewal is stopped: the subscription ends with its period and cannot be cancelled before (both vendors -
     * the gateway refuses the cancellation with GGS-3).
     */
    public static function endsAtPeriodEnd($subscription)
    {
        return $subscription && (int)$subscription->api_order_id > 0 && !(int)$subscription->auto_renew
               && !in_array(strtolower((string)$subscription->status), self::CLOSED, true) && !self::renewalUnpaid($subscription);
    }

    /** The last day of the period: the later of the renewal date and the end date (Y-m-d, '' when not known) */
    public static function periodEnd($subscription)
    {
        $end = '';
        foreach([$subscription->renewal_date, $subscription->period_end] as $date)
        {
            $date = (string)$date;
            $time = ($date !== '' && strpos($date, '0000-00-00') !== 0) ? strtotime($date) : false;
            if($time && $time > 0 && date('Y-m-d', $time) > $end)
            {
                $end = date('Y-m-d', $time);
            }
        }

        return $end;
    }

    /** The end of the period for the client, in the format of the client pages ('-' when not known) */
    public static function periodEndText($subscription)
    {
        $end = self::periodEnd($subscription);

        return $end !== '' ? date('F j, Y', strtotime($end)) : '-';
    }

    /**
     * After a refresh: tells the administrator once when the renewal of a subscription could not be charged at the
     * vendor (the reseller's account) - new certificates are not issued until it is paid there.
     */
    public static function reportUnpaidRenewal($serviceId, $wasUnpaid, $subscription)
    {
        if(!$wasUnpaid && self::renewalUnpaid($subscription))
        {
            GeneralSettings::activity('Subscription of service #' . (int)$serviceId . ': the renewal was not paid at the vendor (account balance / card) - no new certificates are issued until it is paid there; certificates issued before stay valid.');
        }
    }
}
