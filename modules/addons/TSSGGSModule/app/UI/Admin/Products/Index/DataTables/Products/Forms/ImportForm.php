<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers\ImportProvider;
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


class ImportForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider                  = ImportProvider::class;
        $this->providerAction            = ImportProvider::ACTION_CREATE;
        $this->providerActionsToValidate = ['create', 'update', 'delete'];
    }

    public function loadHtml(): void
    {
        $ajaxData = Request::get('ajaxData');
        $formData = Request::get('formData');
        $mode     = 'individual';

        if(isset($ajaxData['reloadedBy']) && $ajaxData['reloadedBy'] == 'pricingTypePercent')
        {
            $mode = ((bool)$formData['pricingTypePercent']) ? "percent" : "individual";
        }
        elseif(isset($ajaxData['reloadedBy']) && $ajaxData['reloadedBy'] == 'pricingTypeIndividual')
        {
            $mode = ((bool)$formData['pricingTypeIndividual']) ? "individual" : "percent";
        }

        $this->builder = BuilderCreator::oneColumn($this);

        $this->setId('productImportForm');

        $grid        = new Grid();
        $leftColumn  = new Container();
        $rightColumn = new Container();

        $grid->setRows([
                           [
                               [$leftColumn, 6],
                               [$rightColumn, 6]
                           ]
                       ]);

        $this->builder->addElement($grid);

        $pricingTypeIndividual = (new Switcher())
            ->setName('pricingTypeIndividual')
            ->addClass('switcher-revert')
            ->setId('pricingTypeIndividual')
            ->setDefaultValue('1')
            ->onChange((new Reload($this)));

        $pricingTypePercent = (new Switcher())
            ->setName('pricingTypePercent')
            ->addClass('switcher-revert')
            ->setId('pricingTypePercent')
            ->setDefaultValue('')
            ->onChange((new Reload($this)));

        //$description = (new AlertInfo())->setText($this->translate('description'))->setId('product-import-form-alert');

        $this->builder->addFieldInContainer($leftColumn, $pricingTypeIndividual);
        $this->builder->addFieldInContainer($leftColumn, $pricingTypePercent);
        //$leftColumn->addElement($description);

        $productGroupField = new Dropdown();
        $productGroupField->setName('productGroup');
        $productGroupField->required();
        $productGroupField->setDefaultValueAsFirstOption();
        $this->builder->addFieldInContainer($rightColumn, $productGroupField);
        /*
                $currencyField = new Dropdown();
                $currencyField->setName('currency');
                $currencyField->required();
                $this->builder->addFieldInContainer($rightColumn, $currencyField);
        */

        if(!Helpers::getCurrencyIdByCode('USD')) //If USD not defined
        {
            $this->builder->addFieldInContainer(
                $rightColumn,
                (new FormInputText())
                    ->setName('rate')
                    ->required()
            );

            $rightColumn->addElement(
                (new AlertInfo())
                    ->setText($this->translate('info', [':defaultCurrency' => Helpers::getDefaultCurrencyCode()]))
                    ->setId('financial-settings-form-info')
            );
        }

        if($mode == 'percent')
        {
            $profitMarginField = new Number();
            $profitMarginField->setDefaultValue(1);
            $profitMarginField->setMin(1);
            $profitMarginField->setMax(1000);
            $profitMarginField->setName('profitMargin');
            $profitMarginField->required();
            $profitMarginField->numeric()->between(1, 1000);
            $this->builder->addFieldInContainer($rightColumn, $profitMarginField, true);
        }

        $idField = new HiddenField();
        $idField->setName('id');
        $this->builder->addFieldInContainer($rightColumn, $idField);
    }
}