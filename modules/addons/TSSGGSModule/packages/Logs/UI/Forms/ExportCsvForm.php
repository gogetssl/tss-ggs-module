<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TSSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers\ExportProvider;

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