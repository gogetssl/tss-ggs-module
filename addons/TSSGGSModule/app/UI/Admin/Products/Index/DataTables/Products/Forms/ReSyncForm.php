<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers\ImportProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers\ReSyncProvider;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertWarning;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Components\RadioButton\RadioButton;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Tagger\Tagger;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;


class ReSyncForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = ReSyncProvider::class;
        $this->providerAction = ReSyncProvider::ACTION_CREATE;
        $this->providerActionsToValidate = ['create', 'update', 'delete'];
    }

    public function loadHtml(): void
    {

    }
}