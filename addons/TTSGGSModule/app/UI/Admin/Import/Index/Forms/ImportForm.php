<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Providers\ImportProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\FilterProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class ImportForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = ImportProvider::class;
        $this->providerAction = ImportProvider::ACTION_CREATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('ImportForm');
        $toolbar = new Toolbar();

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setTitle($this->translate('import'))
                ->setIcon('import')
                ->onClick(
                    (new FormSubmit($this))
                )
        );

        $this->builder->addField(
            (new HiddenField())
                ->setName('mode')
                ->setValue('single')
        );

        $this->builder->addField(
            (new FormInputText())
                ->setTitle($this->translate('remoteOrderId'))
                ->setName('remoteOrderId')
                ->required()
        );

        $this->builder->addField(
            (new Dropdown())
                ->setTitle($this->translate('clientId'))
                ->setName('clientId')
                ->required()
        );

        $this->builder->addField(
            (new Dropdown())
                ->setTitle($this->translate('payMethod'))
                ->setName('payMethod')
                ->required()
        );

        $this->builder->addField(
            (new Dropdown())
                ->setTitle($this->translate('vendor'))
                ->setName('vendor')
                ->required()
        );

        $this->builder->addField(
            (new Switcher())
                ->setTitle($this->translate('generateInvoice'))
                ->setName('generateInvoice')
                ->addClass('switcher-revert')
        );

        $this->addElement($toolbar);
    }
}