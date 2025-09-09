<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers\MassDeleteProvider;

class MenuDeleteForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = MassDeleteProvider::class;
    protected string $providerAction = MassDeleteProvider::ACTION_DELETE;

    public function loadHtml(): void
    {
        $this->builder->addField((new Dropdown())
            ->setMultiple()
            ->setName('types'),
            true
        );

        $this->builder->addField((new Number())
            ->setName('delete_older_than')
            ->setRange(0, 9999)
            ->setDefaultValue(0),
            true
        );
    }
}
