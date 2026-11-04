<?php

namespace ModulesGarden\TSSGGSModule\App\Repositories\Whmcs;

use ModulesGarden\TSSGGSModule\App\Models\CronCheck;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\AddonModule;

class AddonModuleRepository
{
    public function getModuleConfiguration()
    {
        $settings = AddonModule::where('module', 'configuration_TSSGGSModule')
                               ->whereIn('setting', ['vendors', 'credentials', 'test_connection', 'sslSettings', 'financeSettings', 'cronSettings', 'generalSettings', 'certificates_sync_last'])
                               ->get()->toArray();

        $result = [];
        foreach($settings as $name => $setting)
        {
            if($setting['setting'] == 'credentials')
            {
                $result[$setting['setting']] = json_decode(\decrypt($setting['value']), true);
            }
            if($setting['setting'] == 'test_connection')
            {
                $result[$setting['setting']] = $setting['value'];
            }
            if($setting['setting'] == 'vendors')
            {
                // only the vendors of this edition (TheSSLStore Module: TheSSLStore only)
                $vendors = array_values(array_intersect(explode(',', $setting['value']), \ModulesGarden\TSSGGSModule\App\Libs\Edition::vendors()));
                $result[$setting['setting']] = $vendors ?: \ModulesGarden\TSSGGSModule\App\Libs\Edition::vendors();
            }
            if($setting['setting'] == 'sslSettings')
            {
                $result[$setting['setting']] = json_decode($setting['value'], true);
            }
            if($setting['setting'] == 'financeSettings')
            {
                $result[$setting['setting']] = json_decode($setting['value'], true);
            }
            if($setting['setting'] == 'cronSettings')
            {
                $result[$setting['setting']] = json_decode($setting['value'], true);
            }
            if($setting['setting'] == 'generalSettings')
            {
                $result[$setting['setting']] = json_decode($setting['value'], true);
            }
        }

        // credentials of vendors outside the edition stay stored but are not used
        if(isset($result['credentials']) && is_array($result['credentials']))
        {
            $result['credentials'] = array_intersect_key($result['credentials'], array_flip(\ModulesGarden\TSSGGSModule\App\Libs\Edition::vendors()));
        }

        return $result;
    }

    public function updateLastCertificatesSync($timestamp)
    {
        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'certificates_sync_last'],
            ['value' => $timestamp]
        );
    }

    public function getLastCertificatesSync()
    {
        $lastSync = AddonModule::where('module', 'configuration_TSSGGSModule')
                               ->where('setting', 'certificates_sync_last')
                               ->first();

        return $lastSync ? $lastSync->value : null;
    }

    public function finalize()
    {
        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'test_connection'],
            ['value' => 'success']
        );
    }

    public function saveVendor($vendors)
    {
        AddonModule::updateOrInsert(
                ['module' => 'configuration_TSSGGSModule', 'setting' => 'vendors'],
                ['value' => implode(',', $vendors)]
            );
    }

    /** set by "cron.php <Job> --force": the jobs run whatever their schedule says */
    public static $forceRun = false;

    /** job (cron setting key) => [CronCheck type, default frequency] */
    const CRON_JOBS = [
        'cron1' => ['ProductPricingUpdate', '1d'],
        'cron2' => ['SSLCertificates', '1d'],
        'cron3' => ['RenewalNotifyCertificate', '1d'],
        'cron5' => ['SSLCertificatesProcessing', '5m'],
    ];

    /** seconds between two runs of a frequency */
    const CRON_INTERVALS = [
        '5m' => 300, '10m' => 600, '30m' => 1800, '1h' => 3600, '3h' => 10800, '6h' => 21600, '12h' => 43200,
        '1d' => 86400, '1w' => 604800, '1mo' => 2592000,
    ];

    /**
     * Whether the job is due. It is when the chosen frequency has passed since its last run (so the exact minute the
     * cron line fires does not matter: every 5 minutes, hourly or by the WHMCS cron all work). A job that ended with an
     * error is tried again after 30 minutes (or its frequency when that is shorter). "never" switches it off.
     */
    public function checkCron($name)
    {
        if(self::$forceRun)
        {
            return true;
        }

        // $name is the setting key (cron1 ...); an unknown name or an unset frequency uses the job's default
        $job =isset(self::CRON_JOBS[$name]) ? self::CRON_JOBS[$name] : null;
        if($job === null)
        {
            return false;
        }

        $setting = AddonModule::where('module', 'configuration_TSSGGSModule')->where('setting', 'cronSettings')->first();
        $cronSettings = isset($setting->value) && !empty($setting->value) ? (array)json_decode($setting->value, true) : [];
        $frequency = isset($cronSettings[$name]) && $cronSettings[$name] !== '' ? $cronSettings[$name] : $job[1];

        if($frequency === 'never' || !isset(self::CRON_INTERVALS[$frequency]))
        {
            return false;
        }

        $cronCheck = CronCheck::where('type', $job[0])->first();
        if(!$cronCheck || empty($cronCheck->last_run))
        {
            return true;
        }

        $interval = self::CRON_INTERVALS[$frequency];
        if(!empty($cronCheck->last_error))
        {
            $interval = min($interval, 1800);
        }

        // a little less than the interval: a cron line that fires every 5 minutes must not skip a run by seconds
        return time() - strtotime((string)$cronCheck->last_run) >= $interval - 60;
    }

    public function saveCredentials($sslStoreLivePartnerCode, $sslStoreLiveAuthToken, $goGetSslLivePartnerCode, $goGetSslLiveAuthToken,$sslStoreTestPartnerCode, $sslStoreTestAuthToken, $operationMode)
    {
        $sslStoreLivePartnerCode = trim($sslStoreLivePartnerCode);
        $sslStoreLiveAuthToken   = trim($sslStoreLiveAuthToken);

        $sslStoreTestPartnerCode = trim($sslStoreTestPartnerCode);
        $sslStoreTestAuthToken   = trim($sslStoreTestAuthToken);

        $goGetSslLivePartnerCode = trim($goGetSslLivePartnerCode);
        $goGetSslLiveAuthToken   = trim($goGetSslLiveAuthToken);

        $credentials = [
            'ggs' => [
                'PartnerCode' => $goGetSslLivePartnerCode ?: "",
                'AuthToken'   => $goGetSslLiveAuthToken ?: "",
            ],
            'tss' => [
                'PartnerCode' => $sslStoreLivePartnerCode ?: "",
                'AuthToken'   => $sslStoreLiveAuthToken ?: "",
                'TestPartnerCode' => $sslStoreTestPartnerCode ?: "",
                'TestAuthToken'   => $sslStoreTestAuthToken ?: "",
                'OperationMode' => $operationMode ?: "",
            ],
        ];

        // a vendor outside the edition has no fields on the settings page: keep what is stored for it
        $stored = AddonModule::where('module', 'configuration_TSSGGSModule')->where('setting', 'credentials')->value('value');
        $stored = $stored ? (json_decode(\decrypt($stored), true) ?: []) : [];
        foreach(array_keys($credentials) as $vendor)
        {
            if(!in_array($vendor, \ModulesGarden\TSSGGSModule\App\Libs\Edition::vendors(), true) && isset($stored[$vendor]))
            {
                $credentials[$vendor] = $stored[$vendor];
            }
        }

        $credentialsJson = json_encode($credentials);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'credentials'],
            ['value' => \encrypt($credentialsJson)]
        );
    }

    public function saveSslSettings($data)
    {
        // merged into the stored settings: the SSL and the automated SSL page each save their own part
        $configuration = $this->getModuleConfiguration();
        $stored        = isset($configuration['sslSettings']) && is_array($configuration['sslSettings']) ? $configuration['sslSettings'] : [];
        $data          = json_encode(array_merge($stored, (array)$data));

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'sslSettings'],
            ['value' => $data]
        );
    }

    public function saveCronSettings($data)
    {
        // each job has its own small form: merged into the stored frequencies
        $stored = AddonModule::where('module', 'configuration_TSSGGSModule')->where('setting', 'cronSettings')->value('value');
        $stored = $stored ? (array)json_decode($stored, true) : [];
        $data   = json_encode(array_merge($stored, array_intersect_key((array)$data, self::CRON_JOBS)));

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'cronSettings'],
            ['value' => $data]
        );
    }

    public function saveGeneralSettings($data)
    {
        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'generalSettings'],
            ['value' => json_encode($data)]
        );
    }

    public function saveFinanceSettings($data)
    {
        $data = json_encode($data);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TSSGGSModule', 'setting' => 'financeSettings'],
            ['value' => $data]
        );
    }
}
