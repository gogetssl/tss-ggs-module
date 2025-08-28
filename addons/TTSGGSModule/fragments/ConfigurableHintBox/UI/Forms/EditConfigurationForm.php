<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Providers\ConfigurationProvider;

class EditConfigurationForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = ConfigurationProvider::class;
    protected string $providerAction = CrudProvider::ACTION_UPDATE;

    public function loadHtml(): void
    {
        $this->builder->createField(Switcher::class, 'hideHintBox', true);
        $this->builder->createField(HiddenField::class, 'widgetId');
    }
}