<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Providers\FilterProvider;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonBasic;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class FilterForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = FilterProvider::class;
        $this->providerAction = FilterProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder        = BuilderCreator::twoColumns($this);
        $this->setId('datewiseSalesFilterForm');
        $toolbar = new Toolbar();

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setTitle($this->translate('search'))
                ->setIcon('magnify')
                ->onClick(
                    (new ReloadById('datewiseSalesDataTable'))
                        ->withDataFromFormById($this->getId())
                )
                ->onClick(
                    (new ReloadById('DatewiseSalesHeader'))
                        ->withDataFromFormById($this->getId())
                )
        );

        $fromDateField = new DatePicker();
        $fromDateField->setTitle($this->translate('fromDate'));
        $fromDateField->setName('fromDate');
        $this->builder->addField($fromDateField);


        $toDateField = new DatePicker();
        $toDateField->setTitle($this->translate('toDate'));
        $toDateField->setName('toDate');
        $this->builder->addField($toDateField);

        $ajaxData = Request::get('ajaxData');

        if((int)$ajaxData['advancedSearch'] === 1)
        {
            $this->builder->addField((new Dropdown())->setName('brand')->setId('brandField')->setDefaultValueAsFirstOption());
            $this->builder->addField((new Dropdown())->setName('productId')->setId('productIdField')->setDefaultValueAsFirstOption());
            $this->builder->addField((new Dropdown())->setName('sslStatus')->setId('sslStatusField')->setDefaultValueAsFirstOption());
            $this->builder->addField((new FormInputText())->setName('vendorOrderId')->setId('vendorOrderIdField'));

            $toolbar->addElement(
                (new ButtonBasic())
                    ->setTitle($this->translate('basicSearch'))
                    ->onClick(
                        (new Reload($this))
                            ->withParams(['advancedSearch' => 0])
                    )
            );

        }
        else
        {
            $toolbar->addElement(
                (new ButtonBasic())
                    ->setTitle($this->translate('advancedSearch'))
                    ->onClick(
                        (new Reload($this))
                            ->withParams(['advancedSearch' => 1])
                    )
            );
        }

        $this->addElement($toolbar);
    }
}