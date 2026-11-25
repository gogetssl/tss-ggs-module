<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Libs\Support\GeneralSettings;

class GeneralSettingsProvider extends Provider
{
    const TEXTS = 'admin.settings.index.providers.general_settings_provider';

    const SWITCHES = ['renewReuseOrderDetails', 'downloadFormat_zip', 'downloadFormat_crt', 'downloadFormat_ca', 'downloadFormat_fullchain', 'downloadFormat_nginx', 'downloadFormat_apache', 'downloadFormat_pkcs7', 'clientSummary', 'certificatesByDomain', 'sslTools', 'whmcsActivityLog', 'moduleLog', 'certificateLog', 'internalLog', 'eventLog', 'terminateCancelsOrder', 'requireAgreement', 'useDefaultWebServer', 'keepMarginOnSync', 'belowCostEmail', 'hideInfoMessages', 'issuedMessageOn_sectigo', 'issuedMessageOn_digicert'];

    /** WHMCS client area templates offered in "Show the SSL menu on" (any other template name can be typed in) */
    const SIDEBAR_PAGES = ['clientareahome', 'clientareaproducts', 'clientareaproductdetails', 'clientareadomains', 'clientareadomaindetails', 'clientareainvoices',
                           'supportticketslist', 'viewticket', 'clientareadetails', 'knowledgebase', 'announcements'];

    public function read()
    {
        foreach(GeneralSettings::all() as $key => $value)
        {
            $this->data[$key] = in_array($key, self::SWITCHES, true) ? GeneralSettings::enabled($key) : $value;
        }

        // client area pages for the SSL menu: the usual ones by name, plus any template name saved before
        $this->data['sidebarTemplates'] = array_values(array_filter(array_map('trim', explode(',', (string)GeneralSettings::get('sidebarTemplates')))));
        $pages = [];
        foreach(self::SIDEBAR_PAGES as $template)
        {
            $pages[$template] = $this->translate('page_' . $template);
        }
        foreach($this->data['sidebarTemplates'] as $template)
        {
            $pages += [$template => $template];
        }
        $this->availableValues['sidebarTemplates'] = $pages;

        // the OpenCorporates token is never sent to the page; the switch is on while a token is saved
        $this->data['openCorporates']      = (string)GeneralSettings::get('openCorporatesToken') !== '';
        $this->data['openCorporatesToken'] = '';
        // WHMCS Module Log: YES only while WHMCS records it too (switched off in WHMCS = nothing is written, so NO)
        $this->data['moduleLog']     = GeneralSettings::enabled('moduleLog') && \TSSGGSModule\App\Libs\Support\WhmcsModuleLog::enabled();
        $this->data['debugServices'] = implode(', ', \TSSGGSModule\App\Libs\Support\LogPolicy::debugServices());
        $this->data['debugHours']    = 4;
        $this->availableValues['apiLogMode'] = self::apiModeOptions($this);
        $this->availableValues['debugHours'] = self::debugHourOptions($this);
        $this->availableValues['expiresSoonDays'] = ['10' => '10', '15' => '15', '30' => '30', '60' => '60', '90' => '90'];
        // availableValues is an ArrayAccess container: the options are built first, then set at once
        $periods = [];
        foreach(array_keys(\TSSGGSModule\App\Libs\Dashboard\SalesSummary::PERIODS) as $period)
        {
            $periods[$period] = \TSSGGSModule\App\Libs\Dashboard\SalesSummary::periodLabel($period);
        }
        $this->availableValues['dashboardPeriod'] = $periods;

        // the module template, the administrator's own Product/Service templates (CertificateService::certificateTemplates)
        // and the saved one, so a choice made before stays visible
        $templates = ['' => $this->translate('moduleTemplate')];
        foreach(\TSSGGSModule\App\Libs\Certificates\CertificateService::certificateTemplates() as $name)
        {
            $templates[$name] = $name;
        }
        $saved = trim((string)\TSSGGSModule\App\Libs\Support\GeneralSettings::get('certificateEmailTemplate'));
        if($saved !== '' && !isset($templates[$saved]))
        {
            $templates[$saved] = $saved;
        }
        $this->availableValues['certificateEmailTemplate'] = $templates;
    }

    public function update()
    {
        $data = $this->formData->toArray();
        // Company Register Search switched on needs a token: the one typed now or the saved one
        if(array_key_exists('openCorporates', $data) && self::on($data['openCorporates'])
            && trim((string)($data['openCorporatesToken'] ?? '')) === '' && (string)GeneralSettings::get('openCorporatesToken') === '')
        {
            return (new Result())->setError($this->translate('openCorporatesTokenMissing'));
        }
        // WHMCS Module Log YES: WHMCS has to record it too, so it is switched on there (WHMCS-wide, logged in the activity
        // log). NO only stops this module sending; WHMCS keeps its own setting for the other modules
        if(array_key_exists('moduleLog', $data) && self::on($data['moduleLog']) && !\TSSGGSModule\App\Libs\Support\WhmcsModuleLog::enabled())
        {
            if(!\TSSGGSModule\App\Libs\Support\WhmcsModuleLog::canEnable())
            {
                return (new Result())->setError($this->translate('whmcsModuleLogNoPermission',
                    [':permission' => \TSSGGSModule\App\Libs\Support\WhmcsModuleLog::PERMISSION]));
            }
            \TSSGGSModule\App\Libs\Support\WhmcsModuleLog::enable();
        }
        self::saveGeneral($data);
    }

    protected static function on($value)
    {
        return !empty($value) && !in_array($value, ['off', 'false', '0'], true);
    }

    /**
     * Saves the posted general settings (any subset: the settings are spread over several pages) into the stored ones
     */
    public static function saveGeneral(array $data)
    {
        foreach(self::SWITCHES as $key)
        {
            if(array_key_exists($key, $data))
            {
                $data[$key] = !empty($data[$key]) && !in_array($data[$key], ['off', 'false', '0'], true);
            }
        }
        if(array_key_exists('dashboardPeriod', $data) && !array_key_exists((string)$data['dashboardPeriod'], \TSSGGSModule\App\Libs\Dashboard\SalesSummary::PERIODS))
        {
            unset($data['dashboardPeriod']);
        }
        if(array_key_exists('expiresSoonDays', $data))
        {
            $data['expiresSoonDays'] = max(1, (int)$data['expiresSoonDays']);
        }
        if(array_key_exists('sidebarTemplates', $data))
        {
            $pages = is_array($data['sidebarTemplates']) ? $data['sidebarTemplates'] : explode(',', (string)$data['sidebarTemplates']);
            $data['sidebarTemplates'] = implode(',', array_unique(array_filter(array_map(function($page) {
                return preg_replace('/[^a-z0-9_-]/i', '', (string)$page);
            }, $pages))));
        }

        // Company Register Search off: the saved token is removed (no unused secret stays stored)
        if(array_key_exists('openCorporates', $data))
        {
            if(!self::on($data['openCorporates']))
            {
                $data['openCorporatesToken'] = '-';
            }
            unset($data['openCorporates']);
        }
        // OpenCorporates token: empty keeps the saved one, "-" removes it, anything else is saved encrypted
        if(array_key_exists('openCorporatesToken', $data))
        {
            $token = trim((string)$data['openCorporatesToken']);
            if($token === '')
            {
                unset($data['openCorporatesToken']);
            }
            else
            {
                $data['openCorporatesToken'] = $token === '-' ? '' : \encrypt($token);
            }
        }

        // debug mode: the service list and for how long (the hours are not stored, only the end time)
        if(array_key_exists('debugServices', $data))
        {
            $data = array_merge($data, \TSSGGSModule\App\Libs\Support\LogPolicy::debugSettings($data['debugServices'], $data['debugHours'] ?? 0));
        }

        GeneralSettings::save($data);
    }

    public static function apiModeOptions($provider): array
    {
        $options = [];
        foreach(\TSSGGSModule\App\Libs\Support\LogPolicy::API_MODES as $mode)
        {
            $options[$mode] = $provider->translate('apiMode_' . $mode, [], ['admin.log_policy']);
        }

        return $options;
    }

    public static function debugHourOptions($provider): array
    {
        $options = [];
        foreach(\TSSGGSModule\App\Libs\Support\LogPolicy::DEBUG_HOURS as $hours)
        {
            $options[(string)$hours] = $provider->translate('hours_' . $hours, [], ['admin.log_policy']);
        }

        return $options;
    }
}
