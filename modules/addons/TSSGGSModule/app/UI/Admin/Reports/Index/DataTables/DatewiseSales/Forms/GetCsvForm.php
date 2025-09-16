<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers\GetCsvProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers\FilterProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class GetCsvForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = GetCsvProvider::class;
        $this->providerAction = GetCsvProvider::ACTION_CREATE;
    }

    public function loadHtml(): void
    {

    }
}