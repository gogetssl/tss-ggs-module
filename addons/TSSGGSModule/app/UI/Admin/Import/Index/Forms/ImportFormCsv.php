<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Providers\ImportProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\FilterProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\UploadField\UploadField;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class ImportFormCsv extends Form implements AdminAreaInterface, AjaxComponentInterface
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
        $this->setId('ImportFormCsv');
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
                ->setValue('csv')
                ->required()
        );

        $this->builder->addField(
            (new UploadField())
                ->setName('csv')
                ->setAllowedFileTypes(['text/csv'])
                //->required()
        );

        $this->builder->addField(
            (new Dropdown())
                ->setTitle($this->translate('vendor'))
                ->setName('vendor')
                ->required()
        );

        $this->builder->addField(
            (new Switcher())
                ->setName('generateInvoice')
                ->addClass('switcher-revert')
        );

        $this->addElement($toolbar);
    }
}