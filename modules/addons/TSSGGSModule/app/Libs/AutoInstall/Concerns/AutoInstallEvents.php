<?php

namespace TSSGGSModule\App\Libs\AutoInstall\Concerns;

use TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallReport;

/**
 * What starts AutoInstall work: configuration, manual reissue, renewal, the automatic reissue scan and its settings.
 * Part of AutoInstallHelpers (used there as a trait).
 */
trait AutoInstallEvents
{
    /**
     * Client submitted the SSL configuration with "Autoinstall".
     */
    public static function onConfigured($serviceId, $userId, array $config, array $validateTaskData)
    {
        self::create($serviceId, $config);
        AutoInstallQueue::cancelOpenTasks($serviceId, 'Replaced by a new configuration');
        AutoInstallQueue::addTask($userId, $serviceId, 'validate', $validateTaskData);
        AutoInstallReport::start($serviceId, AutoInstallReport::KIND_ORDER, (string)($config['domain'] ?? ''));

        self::log($userId, $serviceId, 'success', 'SSL order placed for domain: ' . $config['domain'] .
            ' (API order #' . $validateTaskData['api_order_id'] . ', validation: ' . strtoupper($config['dcv_method']) . ') - AutoInstall validation task created');
    }

    /**
     * The client reissued the certificate manually with a new CSR - the stored private key no longer matches.
     */
    public static function onManualReissue($serviceId)
    {
        $state = self::get($serviceId);
        if(!$state || $state['status'] === self::STATUS_DISABLED)
        {
            return;
        }

        $userId = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('userid');

        AutoInstallQueue::cancelOpenTasks($serviceId, 'Certificate reissued manually');
        $state['last_error'] = '';
        self::save($serviceId, $state, self::STATUS_DISABLED);

        self::log($userId, $serviceId, 'info', 'Certificate reissued manually with a new CSR - AutoInstall has been disabled for this service. ' .
            'The new certificate has to be installed manually.');
    }

    /**
     * The certificate was renewed (same CSR / private key) - install the renewed certificate as well.
     * $targetServiceId differs from $sourceServiceId when the renewal created a new WHMCS service.
     */
    /**
     * Called by the Renew action once the renewal order is placed (same as SSLCENTERWHMCS Renew::afterRenew):
     *  - AutoInstall: queue validation + installation of the renewed certificate, send "Renewal Started",
     *  - otherwise:   send "Certificate Autorenewed".
     */
    public static function afterRenewal($sourceServiceId, $targetServiceId, $newOrderId)
    {
        $state      = self::get($sourceServiceId);
        $certExpiry = isset($state['valid_till']) ? (string)$state['valid_till'] : '';
        $queued     = self::onRenewal($sourceServiceId, $targetServiceId, $newOrderId);

        $hosting = Capsule::table('tblhosting')->where('id', $targetServiceId)->first();
        $userId  = $hosting ? (int)$hosting->userid : 0;
        $domain  = $hosting ? (string)$hosting->domain : '';

        if($queued)
        {
            $daysLeft = $certExpiry ? (int)round((strtotime($certExpiry) - time()) / 86400) : 0;
            if(!AutoInstallEmailTemplates::sendRenewalStartedEmail($targetServiceId, $state['domain'], $certExpiry, $daysLeft))
            {
                self::log($userId, $targetServiceId, 'error', '[Renew] Failed to send Renewal Started email to the client');
            }
            return;
        }

        $commonName = $domain;
        $request    = Request::where('serviceid', $targetServiceId)->where('name', 'renewOrder')->orderBy('id', 'desc')->first();
        if($request && $request->request)
        {
            $requestData = json_decode(\decrypt($request->request), true);
            if(!empty($requestData['common_name']['name']))
            {
                $commonName = $requestData['common_name']['name'];
            }
        }

        AutoInstallEmailTemplates::sendRenewalAutorenewedEmail($targetServiceId, $domain, (string)$newOrderId, $commonName);
    }

    /**
     * Queues the installation of the renewed certificate. Returns true when AutoInstall is used for the service.
     */
    public static function onRenewal($sourceServiceId, $targetServiceId, $newOrderId)
    {
        $state = self::get($sourceServiceId);
        if(!$state || $state['status'] === self::STATUS_DISABLED || (int)$newOrderId <= 0)
        {
            return false;
        }

        $userId = (int)Capsule::table('tblhosting')->where('id', $targetServiceId)->value('userid');

        $config               = $state;
        $config['last_error'] = '';
        unset($config['reissue_for_valid_till']);

        if((int)$sourceServiceId === (int)$targetServiceId)
        {
            self::save($targetServiceId, $config, self::STATUS_AWAITING);
        }
        else
        {
            self::create($targetServiceId, $config);
        }

        AutoInstallQueue::cancelOpenTasks($targetServiceId, 'Replaced by renewal');

        $taskData = [
            'domain'       => $config['domain'],
            'api_order_id' => (int)$newOrderId,
            'is_renewal'   => true,
            'is_wildcard'  => !empty($config['is_wildcard']),
        ];
        if(isset($config['dcv_method']) && $config['dcv_method'] === 'dns')
        {
            $taskData['dns_check_start'] = time();
        }
        if(!empty($config['dns_fallback_email']))
        {
            $taskData['dns_fallback_email'] = $config['dns_fallback_email'];
        }
        AutoInstallQueue::addTask($userId, $targetServiceId, 'validate', $taskData);
        AutoInstallReport::start($targetServiceId, AutoInstallReport::KIND_RENEWAL, (string)$config['domain']);

        self::log($userId, $targetServiceId, 'info', '[Renew] Certificate renewal placed (API order #' . (int)$newOrderId .
            ') - the renewed certificate will be installed automatically on ' . $config['domain']);

        return true;
    }

    // ------------------------------------------------------------------ //
    // Cron
    // ------------------------------------------------------------------ //

    /**
     * Queues a reissue for installed certificates that expire soon while the subscription is still running.
     */
    public static function getWildcardDnsTimeoutMinutes()
    {
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $minutes     = isset($addonConfig['sslSettings']['wildcardDnsTimeoutMinutes']) ? (int)$addonConfig['sslSettings']['wildcardDnsTimeoutMinutes'] : 0;

        return $minutes > 0 ? $minutes : self::DEFAULT_WILDCARD_DNS_TIMEOUT_MINUTES;
    }

    public static function getAutoReissueDaysBeforeExpire()
    {
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $sslSettings = isset($addonConfig['sslSettings']) && is_array($addonConfig['sslSettings']) ? $addonConfig['sslSettings'] : [];

        if(!isset($sslSettings['autoReissueDaysBeforeExpire']) || $sslSettings['autoReissueDaysBeforeExpire'] === '')
        {
            return self::DEFAULT_AUTO_REISSUE_DAYS_BEFORE_EXPIRE;
        }

        return max(0, (int)$sslSettings['autoReissueDaysBeforeExpire']);
    }

    /**
     * The certificate of this service is reissued automatically - the client does not need the manual reissue reminder.
     */
    public static function isAutoReissueActive($serviceId)
    {
        if(self::getAutoReissueDaysBeforeExpire() <= 0)
        {
            return false;
        }

        $row = Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->first();

        return $row && in_array($row->status, [self::STATUS_AWAITING, self::STATUS_INSTALLED]);
    }

    protected static function scanAutoReissue()
    {
        $daysBeforeExpire = self::getAutoReissueDaysBeforeExpire();
        if($daysBeforeExpire <= 0)
        {
            return;
        }

        $rows = Request::where('name', self::REQUEST_NAME)->where('status', self::STATUS_INSTALLED)->get();

        foreach($rows as $row)
        {
            $serviceId = (int)$row->serviceid;
            $clientId  = 0;

            try
            {
                $hosting = Capsule::table('tblhosting')->where('id', $serviceId)->first();
                if(!$hosting || $hosting->domainstatus !== 'Active')
                {
                    continue;
                }
                $clientId = (int)$hosting->userid;

                $state     = self::get($serviceId);
                $validTill = isset($state['valid_till']) ? trim((string)$state['valid_till']) : '';
                $validTs   = $validTill ? strtotime($validTill) : false;
                if(!$validTs)
                {
                    continue;
                }

                $daysLeft = (int)floor(($validTs - time()) / 86400);
                if($daysLeft > $daysBeforeExpire)
                {
                    continue;
                }

                // End of the subscription - a renewal (new order) is needed, not a reissue
                $subscriptionEnd = !empty($state['subscription_end']) ? strtotime($state['subscription_end']) : false;
                if(!$subscriptionEnd || $subscriptionEnd <= $validTs + 86400)
                {
                    continue;
                }

                if(isset($state['reissue_for_valid_till']) && $state['reissue_for_valid_till'] === $validTill)
                {
                    continue;
                }

                $remoteId = (int)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->value('remoteid');
                if($remoteId <= 0 || (!empty($state['api_order_id']) && (int)$state['api_order_id'] !== $remoteId))
                {
                    continue;
                }

                if(AutoInstallQueue::hasOpenTask($serviceId))
                {
                    continue;
                }

                AutoInstallQueue::addTask($clientId, $serviceId, 'reissue', [
                    'domain'       => $state['domain'],
                    'api_order_id' => $remoteId,
                    'cert_expiry'  => $validTill,
                ]);

                $state['reissue_for_valid_till'] = $validTill;
                self::save($serviceId, $state, self::STATUS_INSTALLED);
                // after the save above: the certificate in place stays installed, the reissue is the new job
                AutoInstallReport::start($serviceId, AutoInstallReport::KIND_REISSUE, (string)$state['domain']);

                self::log($clientId, $serviceId, 'info', '[AutoReissue] Reissue task created for domain: ' . $state['domain'] .
                    ' - certificate expires ' . $validTill . ' (' . $daysLeft . ' days remaining)');
            }
            catch(\Exception $e)
            {
                self::log($clientId, $serviceId, 'error', '[AutoReissue] Error checking service #' . $serviceId . ': ' . $e->getMessage());
            }
        }
    }

    // ------------------------------------------------------------------ //
    // Validation helpers
    // ------------------------------------------------------------------ //
}
