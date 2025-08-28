<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers\GetCsvProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers\FilterProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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