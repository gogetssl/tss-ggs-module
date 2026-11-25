<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates\Concerns;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\CodeGuardService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\CodeSigningService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\DocumentSigningService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\LeiService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\PciService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\SmimeService;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\IncompleteOrder;

/**
 * Cancellation and the renewal reminder lead time of a certificate service.
 * Part of CertificateService (used there as a trait).
 */
trait CertificateLifecycle
{
    /**
     * Cancels the vendor order of any category (Terminate, admin "Cancel order")
     */
    public static function cancel($serviceId, $reason)
    {
        switch(self::category($serviceId))
        {
            case self::CATEGORY_ACME:
                $subscription = AcmeSubscription::getByServiceId($serviceId);
                if(!$subscription || (int)$subscription->acme_id <= 0 || in_array($subscription->status, ['cancelled', 'terminated'], true))
                {
                    return false;
                }
                self::api($serviceId)->cancelItem((int)$subscription->acme_id, $reason, self::CATEGORY_ACME);
                $subscription->status       = 'cancelled';
                $subscription->cancelled_at = date('Y-m-d H:i:s');
                $subscription->save();
                break;

            case self::CATEGORY_AIS:
                $subscription = AisSubscription::getByServiceId($serviceId);
                if(!$subscription || (int)$subscription->ais_item_id <= 0 || in_array($subscription->status, ['cancelled', 'terminated'], true))
                {
                    return false;
                }
                self::api($serviceId)->cancelAisSubscription((int)$subscription->ais_item_id, $reason);
                $subscription->status = 'cancelled';
                $subscription->save();
                break;

            case self::CATEGORY_CS:
                if(!CodeSigningService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_SMIME:
                if(!SmimeService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_CODE_GUARD:
                if(!CodeGuardService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_DS:
                if(!DocumentSigningService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_PCI:
                if(!PciService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            case self::CATEGORY_LEI:
                if(!LeiService::cancel($serviceId, $reason))
                {
                    return false;
                }
                break;

            default:
                // bought at payment, details never submitted: that order is cancelled (IncompleteOrder)
                if(IncompleteOrder::cancel($serviceId, $reason))
                {
                    Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled']);
                    break;
                }
                $ssl = self::sslOrder($serviceId);
                if(!$ssl || empty($ssl->remoteid) || in_array(self::status($serviceId), AutoInstallHelpers::FINAL_ORDER_STATUSES, true))
                {
                    return false;
                }
                self::api($serviceId)->cancelItem((int)$ssl->remoteid, $reason);
                Request::where('serviceid', (int)$serviceId)->where('name', 'addOrder')->update(['status' => 'cancelled']);
                Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->update(['status' => 'cancelled']);
                // WHMCS's own SSL order list shows the order as cancelled (the syncs only take "Configuration Submitted")
                Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled']);
                try
                {
                    AutoInstallQueue::cancelOpenTasks($serviceId, 'Order cancelled');
                }
                catch(\Throwable $e)
                {
                }
        }

        self::event($serviceId, 'info', 'Vendor order cancelled (' . $reason . ')');
        self::forgetPrivateKeys($serviceId);

        return true;
    }

    /**
     * Per-service override, null = not set
     */
    public static function renewalDaysOverride($serviceId)
    {
        $value = Request::where('serviceid', (int)$serviceId)->where('name', 'renewalDays')->value('request');

        return $value === null || $value === '' ? null : (int)$value;
    }

    public static function setRenewalDaysOverride($serviceId, $days)
    {
        if($days === null || trim((string)$days) === '')
        {
            Request::where('serviceid', (int)$serviceId)->where('name', 'renewalDays')->delete();

            return;
        }
        if(!ctype_digit(trim((string)$days)) || (int)$days > self::RENEWAL_DAYS_MAX)
        {
            throw new \InvalidArgumentException('Additional days must be between 0 and ' . self::RENEWAL_DAYS_MAX);
        }
        Request::updateOrInsert(['serviceid' => (int)$serviceId, 'name' => 'renewalDays'],
            ['request' => (string)(int)$days, 'status' => 'set', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Days sent with a Sectigo renewal order: the service override, then the "Additional Days For Renewal Order" setting,
     * otherwise (and for every other CA) the days left on the current certificate
     */
    public static function renewalDays($serviceId, $remainingDays)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        if(!$packageId || !self::isSectigo($packageId))
        {
            return max(0, (int)$remainingDays);
        }

        $override = self::renewalDaysOverride($serviceId);
        if($override !== null)
        {
            return $override;
        }
        $global = (int)((new AddonModuleRepository())->getModuleConfiguration()['sslSettings']['additionalDaysForRenewalOrder'] ?? 0);

        // without a setting, the days left - but Sectigo adds no more than 30, and a renewal paid on the first day of a
        // 30-day lead counts 31
        return $global > 0 ? $global : min(self::RENEWAL_DAYS_MAX, max(0, (int)$remainingDays));
    }
}
