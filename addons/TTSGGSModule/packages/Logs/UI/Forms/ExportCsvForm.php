<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TTSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers\ExportProvider;

class ExportCsvForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = ExportProvider::class;
    protected string $providerAction = ExportProvider::ACTION_CREATE;

    public function loadHtml(): void
    {
        $this->builder->addField((new DatePicker())->setName('from')->addValidator('date'),true);
        $this->builder->addField((new DatePicker())->setName('to')->addValidator('date'),true);
        $this->builder->addField(
            (new Dropdown())
                ->setMultiple()
                ->setName('types')
                ->setPlaceholder($this->translate('allTypes')),
            true);
    }

}