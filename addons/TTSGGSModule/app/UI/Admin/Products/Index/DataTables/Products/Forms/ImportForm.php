<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers\ImportProvider;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertWarning;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Number\Number;
use ModulesGarden\TTSGGSModule\Components\RadioButton\RadioButton;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Tagger\Tagger;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;


class ImportForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = ImportProvider::class;
        $this->providerAction = ImportProvider::ACTION_CREATE;
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
        $this->builder->addFieldInContainer($rightColumn, $productGroupField);

        $currencyField = new Dropdown();
        $currencyField->setName('currency');
        $currencyField->required();
        $this->builder->addFieldInContainer($rightColumn, $currencyField);

        $this->builder->addFieldInContainer(
            $rightColumn,
            (new FormInputText())
                ->setName('rate')
        );

        $rightColumn->addElement(
            (new AlertInfo())
                ->setText($this->translate('info'))
                ->setId('financial-settings-form-info')
        );

        if($mode == 'percent')
        {
            $profitMarginField = new Number();
            $profitMarginField->setDefaultValue(1);
            $profitMarginField->setMin(1);
            $profitMarginField->setMax(1000);
            $profitMarginField->setName('profitMargin');
            $profitMarginField->required();
            $profitMarginField->numeric()->between(1,1000);
            $this->builder->addFieldInContainer($rightColumn, $profitMarginField, true);
        }

        $idField = new HiddenField();
        $idField->setName('id');
        $this->builder->addFieldInContainer($rightColumn, $idField);
    }
}