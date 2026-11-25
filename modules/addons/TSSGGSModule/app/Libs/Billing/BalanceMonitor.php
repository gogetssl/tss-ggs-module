<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Billing;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * Low reseller balance warning: GET /profile of every connected vendor account (Edition::vendors) is read once an
 * hour by the module cron (RunAll) and right after the Finance settings are saved. When the money left falls below
 * the amount the reseller chose (Settings > Finance > Reseller balance, 0 = off), or the account is not active, the
 * WHMCS administrators get an e-mail: at most one a day per account while it stays low, a new one after a top-up and
 * a new drop.
 *
 * The money left depends on how the account pays (profile billing.terms):
 *  - prepaid: the balance;
 *  - credit:  the balance plus the credit limit (the balance can be negative), no limit = no check;
 *  - invoice: not checked - a monthly invoice account is never blocked by its balance.
 * TheSSLStore always answers USD and status null; GoGetSSL may answer another currency (EUR) and a status.
 */
class BalanceMonitor
{
    const CHECK_EVERY = 3600;   // seconds between two automatic checks
    const MAIL_EVERY  = 86400;  // seconds between two e-mails for an account that stays low

    /** Settings key of a vendor's warning amount: balanceThresholdTss / balanceThresholdGgs */
    public static function thresholdKey($vendor): string
    {
        return 'balanceThreshold' . ucfirst(strtolower((string)$vendor));
    }

    public static function threshold($vendor): float
    {
        return max(0, (float)GeneralSettings::get(self::thresholdKey($vendor)));
    }

    /**
     * The last check of a vendor account: currency, balance, terms, credit_limit, status, left (null = not checked),
     * low, checked (timestamp), error; [] before the first check
     */
    public static function info($vendor): array
    {
        $all = (array)GeneralSettings::get('balanceInfo');

        return (array)($all[strtolower((string)$vendor)] ?? []);
    }

    /** The money left to order with, null when the terms do not limit orders (invoice, credit without limit) */
    public static function left(array $billing)
    {
        $terms   = strtolower((string)($billing['terms'] ?? 'prepaid'));
        $balance = (float)($billing['balance'] ?? 0);
        if($terms === 'invoice')
        {
            return null;
        }
        if($terms === 'credit')
        {
            return isset($billing['credit_limit']) && $billing['credit_limit'] !== null ? $balance + (float)$billing['credit_limit'] : null;
        }

        return $balance;
    }

    /**
     * Checks every connected vendor account that is due (or all of them with $force). Errors are kept in the info
     * of the account and never thrown: a cron run or a settings save must not fail because of it.
     */
    public static function run($force = false, ?int $timeout = null)
    {
        foreach(Edition::vendors() as $vendor)
        {
            $vendor = strtolower((string)$vendor);
            $info   = self::info($vendor);
            if(!$force && time() - (int)($info['checked'] ?? 0) < self::CHECK_EVERY)
            {
                continue;
            }
            self::check($vendor, $timeout);
        }
    }

    /** @param int|null $timeout seconds to wait for the gateway (a page passes SSLTrustCenterApi::PAGE_TIMEOUT) */
    protected static function check($vendor, ?int $timeout = null)
    {
        $previous = self::info($vendor);
        $info     = ['checked' => time()] + array_intersect_key($previous, array_flip(['low', 'mailed']));
        try
        {
            $api     = SSLTrustCenterApi::forVendor($vendor);
            $profile = (array)($timeout ? $api->withTimeout($timeout) : $api)->getProfile();
            $billing = (array)($profile['billing'] ?? []);
            $info   += [
                'currency'     => strtoupper((string)($billing['currency'] ?? 'USD')) ?: 'USD',
                'balance'      => (float)($billing['balance'] ?? 0),
                'terms'        => strtolower((string)($billing['terms'] ?? 'prepaid')) ?: 'prepaid',
                'credit_limit' => isset($billing['credit_limit']) ? (float)$billing['credit_limit'] : null,
                'status'       => isset($profile['status']) ? strtolower((string)$profile['status']) : null,
                'left'         => self::left($billing),
                'error'        => '',
            ];
        }
        catch(\Throwable $e)
        {
            // keep the last known figures, show the error next to them
            $info = array_merge($previous, ['checked' => time(), 'error' => substr($e->getMessage(), 0, 300)]);
            self::save($vendor, $info);

            return;
        }

        $threshold   = self::threshold($vendor);
        $inactive    = $info['status'] !== null && $info['status'] !== 'active';
        $lowBalance  = $threshold > 0 && $info['left'] !== null && $info['left'] < $threshold;
        $low         = $inactive || $lowBalance;
        $wasLow      = !empty($previous['low']);
        $info['low'] = $low;
        if(!$low)
        {
            unset($info['mailed']);
        }
        elseif(!$wasLow || time() - (int)($previous['mailed'] ?? 0) >= self::MAIL_EVERY)
        {
            if(self::mail($vendor, $info, $threshold, $inactive))
            {
                $info['mailed'] = time();
            }
        }
        self::save($vendor, $info);
    }

    protected static function save($vendor, array $info)
    {
        $all          = (array)GeneralSettings::get('balanceInfo');
        $all[$vendor] = $info;
        GeneralSettings::save(['balanceInfo' => $all]);
    }

    public static function money($amount, $currency): string
    {
        return number_format((float)$amount, 2, '.', ',') . ' ' . $currency;
    }

    protected static function mail($vendor, array $info, $threshold, $inactive): bool
    {
        $brand = Edition::brandOf($vendor);
        if($inactive)
        {
            $subject = Edition::name() . ': your ' . $brand . ' reseller account is ' . $info['status'];
            $text    = '<p>Your ' . htmlspecialchars($brand) . ' reseller account has the status <b>' . htmlspecialchars((string)$info['status'])
                . '</b>. Orders are only accepted from an active account, so new and renewal orders of your clients will fail.</p>'
                . '<p>Contact ' . htmlspecialchars($brand) . ' to get the account active again.</p>';
        }
        else
        {
            $credit  = $info['terms'] === 'credit';
            $subject = Edition::name() . ': ' . $brand . ' reseller balance is low (' . self::money($info['left'], $info['currency']) . ' left)';
            $text    = '<p>' . ($credit
                    ? 'Your ' . htmlspecialchars($brand) . ' credit left is <b>' . self::money($info['left'], $info['currency']) . '</b> (balance '
                        . self::money($info['balance'], $info['currency']) . ', credit limit ' . self::money($info['credit_limit'], $info['currency']) . ')'
                    : 'Your ' . htmlspecialchars($brand) . ' reseller balance is <b>' . self::money($info['left'], $info['currency']) . '</b>')
                . ', below the ' . self::money($threshold, $info['currency']) . ' you set for this warning.</p>'
                . '<p>Top up your ' . htmlspecialchars($brand) . ' account: when the money runs out, paid orders of your clients can not be sent to the certificate authority.</p>'
                . '<p>You get this e-mail at most once a day while the balance stays low. Change the amount under Settings &gt; Finance &gt; Reseller balance.</p>';
        }

        $result = localAPI('SendAdminEmail', ['customsubject' => $subject, 'custommessage' => $text, 'type' => 'system']);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Low balance e-mail to the administrators failed: ' . ($result['message'] ?? 'unknown error'));

            return false;
        }
        GeneralSettings::activity($brand . ' low balance e-mail sent to the administrators: ' . strip_tags($subject));

        return true;
    }
}
