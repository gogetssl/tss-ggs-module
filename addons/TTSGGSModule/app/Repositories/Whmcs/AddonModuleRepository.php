<?php

namespace ModulesGarden\TTSGGSModule\App\Repositories\Whmcs;

use ModulesGarden\TTSGGSModule\App\Models\Whmcs\AddonModule;

class AddonModuleRepository
{
    public function getModuleConfiguration()
    {
        $settings = AddonModule::where('module', 'configuration_TTSGGSModule')
                               ->whereIn('setting', ['vendors', 'credentials', 'test_connection', 'sslSettings', 'financeSettings', 'cronSettings', 'certificates_sync_last'])
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
                $result[$setting['setting']] = explode(',', $setting['value']);
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
        }

        return $result;
    }

    public function updateLastCertificatesSync($timestamp)
    {
        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'certificates_sync_last'],
            ['value' => $timestamp]
        );
    }

    public function getLastCertificatesSync()
    {
        $lastSync = AddonModule::where('module', 'configuration_TTSGGSModule')
                               ->where('setting', 'certificates_sync_last')
                               ->first();

        return $lastSync ? $lastSync->value : null;
    }

    public function finalize()
    {
        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'test_connection'],
            ['value' => 'success']
        );
    }

    public function saveVendor($vendors)
    {
        AddonModule::updateOrInsert(
                ['module' => 'configuration_TTSGGSModule', 'setting' => 'vendors'],
                ['value' => implode(',', $vendors)]
            );
    }

    public function checkCron($name)
    {
        $day = date('d');
        $week = date('N');
        $hour = date('H');
        $minute = date('i');

        $setting = AddonModule::where('module', 'configuration_TTSGGSModule')
            ->whereIn('setting', ['cronSettings'])
            ->first();

        if(isset($setting->value) && !empty($setting->value))
        {
            $cronSettings = json_decode($setting->value, true);
            if(isset($cronSettings[$name]) && !empty($cronSettings[$name]) && $cronSettings[$name] != 'never')
            {

                if($cronSettings[$name] == '5m')
                {
                    if($minute == '00' || $minute == '10' || $minute == '20' || $minute == '30' || $minute == '40' || $minute == '50' || $minute == '05' || $minute == '15' || $minute == '25' || $minute == '35' || $minute == '45' || $minute == '55')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '10m')
                {
                    if($minute == '00' || $minute == '10' || $minute == '20' || $minute == '30' || $minute == '40' || $minute == '50')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '30m')
                {
                    if($minute == '00' || $minute == '30')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '1h')
                {
                    if($minute == '00')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '3h')
                {
                    if(
                        ($hour == '00' && $minute == '00') || ($hour == '00' && $minute == '03') || ($hour == '06' && $minute == '00') ||
                        ($hour == '09' && $minute == '00') || ($hour == '12' && $minute == '00') || ($hour == '15' && $minute == '00') ||
                        ($hour == '18' && $minute == '00') || ($hour == '21' && $minute == '00')
                    )
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '6h')
                {
                    if(($hour == '00' && $minute == '00') || ($hour == '06' && $minute == '00') || ($hour == '12' && $minute == '00') || ($hour == '18' && $minute == '00'))
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '12h')
                {
                    if(($hour == '00' && $minute == '00') || ($hour == '12' && $minute == '00'))
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '1d')
                {
                    if($hour == '00' && $minute == '00')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '1w')
                {
                    if($hour == '00' && $minute == '00' && $week == '1')
                    {
                        return true;
                    }
                }

                if($cronSettings[$name] == '1mo')
                {
                    if($hour == '00' && $minute == '00' && $day == '01')
                    {
                        return true;
                    }
                }

            }
        }

        return false;
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

        $credentialsJson = json_encode($credentials);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'credentials'],
            ['value' => \encrypt($credentialsJson)]
        );
    }

    public function saveSslSettings($data)
    {
        $data = json_encode($data);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'sslSettings'],
            ['value' => $data]
        );
    }

    public function saveCronSettings($data)
    {
        $data = json_encode($data);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'cronSettings'],
            ['value' => $data]
        );
    }

    public function saveFinanceSettings($data)
    {
        $data = json_encode($data);

        AddonModule::updateOrInsert(
            ['module' => 'configuration_TTSGGSModule', 'setting' => 'financeSettings'],
            ['value' => $data]
        );
    }
}
