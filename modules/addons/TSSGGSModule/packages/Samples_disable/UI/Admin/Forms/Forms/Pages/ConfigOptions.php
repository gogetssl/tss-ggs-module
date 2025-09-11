<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Pages;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractFormConfigOptions;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\FormWithTabsElements;

class ConfigOptions extends AbstractFormConfigOptions implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->builder->createField(FormInputText::class, 'customconfigoption[text]');
    }
}
