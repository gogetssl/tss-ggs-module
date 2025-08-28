<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;
use ModulesGarden\TTSGGSModule\Core\Validation\Rule;
use ModulesGarden\TTSGGSModule\Packages\Logs\Enums\LogTypes;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class ConfigurationProvider extends CrudProvider
{
    public function read()
    {
        $this->data->set('types', $this->formData->get('types', ModuleSettings::get('logs.types')));
        $this->data->set('auto_prune', $this->formData->get('auto_prune', ModuleSettings::get('logs.auto_prune')));
        $this->data->set('auto_prune_older_than', $this->formData->get('auto_prune_older_than', ModuleSettings::get('logs.auto_prune_older_than')));

        $this->availableValues['types'] = (new LogsTypeTranslator)->getAvailableTranslated();
    }

    public function update()
    {
        $this->validate(
            $this->formData->toArray(),
            [
                'types.*'               => ['sometimes', Rule::in(LogTypes::getAvailable())],
                'auto_prune'            => ['sometimes', Rule::in(0, 1)],
                'auto_prune_older_than' => ['required_if:auto_prune,1', 'numeric', 'min:1']
            ],
            [
                'auto_prune' => [1]
            ]
        );

        ModuleSettings::save([
            'logs.types'                 => $this->formData->get('types', ""),
            'logs.auto_prune'            => $this->formData->get('auto_prune'),
            'logs.auto_prune_older_than' => $this->formData->get('auto_prune_older_than', 0)
        ]);
    }
}
