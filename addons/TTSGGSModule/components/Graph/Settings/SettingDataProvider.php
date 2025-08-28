<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Widget\Graphs\Settings;

use ModulesGarden\TTSGGSModule\Core\Models\ModuleSettings\ModuleSettings;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\UI\Widget\Forms\DataProviders\BaseModelDataProvider;

/**
 * Description of SettingDataProvider
 */
class SettingDataProvider extends BaseModelDataProvider
{
    public function __construct()
    {
        parent::__construct('\ModulesGarden\TTSGGSModule\Core\Models\ModuleSettings\ModuleSettings');
    }

    public function read()
    {
        $data = ModuleSettings::where('setting', Request::get('index', ''))->first();

        if ($data)
        {
            $this->data = json_decode($data->value, true);
        }
        else
        {
            $customParams = json_decode(html_entity_decode(Request::get('customParams', '{}')));
            $defaultFilter = json_decode(html_entity_decode(Request::get('defaultFilter', '{}')));
            $this->data['setting'] = Request::get('index', '');
            if ($customParams->labels && $defaultFilter->displayEditColor)
            {
                foreach ($customParams->labels as $label)
                {
                    $this->data[$label] = '47FF44';
                }
            }
        }
    }

    public function update()
    {
        $query = ModuleSettings::where('setting', $this->formData['setting']);
        if ($query->count() > 0)
        {
            $query->update(['value' => json_encode($this->formData)]);
        }
        else
        {
            ModuleSettings::create([
                'setting' => $this->formData['setting'],
                'value'   => json_encode($this->formData),
            ]);
        }
    }
}
