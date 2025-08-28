<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Providers\EditTabsProvider;

class EditTabsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = EditTabsProvider::class;
    protected string $providerAction = CrudProvider::ACTION_UPDATE;

    public function loadHtml(): void
    {
        $this->builder->addField((new Dropdown())->setName("tabsNames")->setMultiple());
    }
}