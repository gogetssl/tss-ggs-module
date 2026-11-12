<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use WHMCS\Database\Capsule;

class GeneralSettingsProvider extends CrudProvider
{
    const SWITCHES = ['renewReuseOrderDetails', 'downloadFormat_zip', 'downloadFormat_crt', 'downloadFormat_ca', 'downloadFormat_fullchain', 'downloadFormat_nginx', 'downloadFormat_apache', 'downloadFormat_pkcs7', 'clientSummary', 'certificatesByDomain', 'sslTools', 'whmcsActivityLog', 'moduleLog', 'certificateLog', 'internalLog', 'eventLog', 'terminateCancelsOrder', 'terminateUnpaidOnExpiry', 'requireAgreement', 'useDefaultWebServer', 'keepMarginOnSync', 'belowCostEmail', 'hideInfoMessages'];

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

        $this->data['debugServices'] = implode(', ', \ModulesGarden\TSSGGSModule\App\Libs\LogPolicy::debugServices());
        $this->data['debugHours']    = 4;
        $this->availableValues['apiLogMode'] = self::apiModeOptions($this);
        $this->availableValues['debugHours'] = self::debugHourOptions($this);
        $this->availableValues['expiresSoonDays'] = ['10' => '10', '15' => '15', '30' => '30', '60' => '60', '90' => '90'];
        $offsets = ['0' => '0', '3' => '3', '7' => '7', '15' => '15', '30' => '30'];
        $this->availableValues['dueDateOffsetRegular']      = $offsets;
        $this->availableValues['dueDateOffsetSubscription'] = $offsets;

        // General e-mail templates; empty = the module's own "Send Certificate" template
        $templates = ['' => $this->translate('moduleTemplate')];
        foreach(Capsule::table('tblemailtemplates')->where('type', 'general')->where('language', '')->orderBy('name')->pluck('name') as $name)
        {
            $templates[$name] = $name;
        }
        $this->availableValues['certificateEmailTemplate'] = $templates;
    }

    public function update()
    {
        self::saveGeneral($this->formData->toArray());
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

        // debug mode: the service list and for how long (the hours are not stored, only the end time)
        if(array_key_exists('debugServices', $data))
        {
            $data = array_merge($data, \ModulesGarden\TSSGGSModule\App\Libs\LogPolicy::debugSettings($data['debugServices'], $data['debugHours'] ?? 0));
        }

        GeneralSettings::save($data);
    }

    public static function apiModeOptions($provider): array
    {
        $options = [];
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\LogPolicy::API_MODES as $mode)
        {
            $options[$mode] = $provider->translate('apiMode_' . $mode, [], ['admin.log_policy']);
        }

        return $options;
    }

    public static function debugHourOptions($provider): array
    {
        $options = [];
        foreach(\ModulesGarden\TSSGGSModule\App\Libs\LogPolicy::DEBUG_HOURS as $hours)
        {
            $options[(string)$hours] = $provider->translate('hours_' . $hours, [], ['admin.log_policy']);
        }

        return $options;
    }
}
