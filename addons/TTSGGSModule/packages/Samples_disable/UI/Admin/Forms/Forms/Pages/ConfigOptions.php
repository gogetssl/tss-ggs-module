<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Pages;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractFormConfigOptions;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\FormWithTabsElements;

class ConfigOptions extends AbstractFormConfigOptions implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->builder->createField(FormInputText::class, 'customconfigoption[text]');
    }
}
