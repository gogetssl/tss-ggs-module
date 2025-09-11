<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers\ConfigurationProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

class EditConfigurationForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = ConfigurationProvider::class;
    protected string $providerAction = ConfigurationProvider::ACTION_UPDATE;

    public function loadHtml(): void
    {
        $this->builder->addField((new Dropdown())
            ->setMultiple()
            ->setName('types'),
            true
        );

        if (!Config::get('logs.auto_prune.enabled', true))
        {
            return;
        }

        $this->builder->addField((new Switcher())
            ->setName('auto_prune')
            ->onChange(new Reload($this)),
            true
        );

        if (!$this->provider()->getValueById('auto_prune'))
        {
            return;
        }

        $this->builder->addField((new Number())
            ->setName('auto_prune_older_than')
            ->setRange(0, 9999)
            ->setDefaultValue(0),
            true
        );

    }
}
