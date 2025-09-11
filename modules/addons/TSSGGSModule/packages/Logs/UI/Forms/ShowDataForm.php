<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\PreBlockArrayPrint\PreBlockArrayPrint;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers\ShowDataProvider;

class ShowDataForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = ShowDataProvider::class;
    protected string $providerAction = CrudProvider::ACTION_DELETE;

    public function loadHtml(): void
    {
        $this->builder->createField(HiddenField::class, 'id');
        $this->builder->createField(PreBlockArrayPrint::class, 'data');
    }
}
