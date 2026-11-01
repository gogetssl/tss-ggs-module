<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use WHMCS\Database\Capsule;

class GeneralSettingsProvider extends CrudProvider
{
    const SWITCHES = ['clientSummary', 'certificatesByDomain', 'whmcsActivityLog', 'moduleLog', 'terminateCancelsOrder', 'requireAgreement'];

    public function read()
    {
        foreach(GeneralSettings::all() as $key => $value)
        {
            $this->data[$key] = in_array($key, self::SWITCHES, true) ? GeneralSettings::enabled($key) : $value;
        }

        $this->availableValues['expiresSoonDays'] = ['10' => '10', '15' => '15', '30' => '30', '60' => '60', '90' => '90'];

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
        $data = $this->formData->toArray();
        foreach(self::SWITCHES as $key)
        {
            $data[$key] = !empty($data[$key]) && !in_array($data[$key], ['off', 'false', '0'], true);
        }
        $data['expiresSoonDays']  = max(1, (int)($data['expiresSoonDays'] ?? 30));
        $data['sidebarTemplates'] = preg_replace('/[^a-z0-9_,-]/i', '', (string)($data['sidebarTemplates'] ?? ''));

        GeneralSettings::save($data);
    }
}
