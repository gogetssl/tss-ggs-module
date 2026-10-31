<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Providers\ResetTaskProvider;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class ResetTaskForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = ResetTaskProvider::class;
    // Default action "read" generates the integrity token for "id" (required by "update") - the modal has to be loaded via modalLoad
    protected string $providerAction = CrudProvider::ACTION_UPDATE;

    public function loadHtml(): void
    {
        $this->builder->createField(HiddenField::class, 'id');
    }
}
