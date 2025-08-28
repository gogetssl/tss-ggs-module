<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers\FilterProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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
        $this->builder = BuilderCreator::twoColumns($this);
        $this->setId('renewalFilterForm');
        $toolbar = new Toolbar();

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setTitle($this->translate('search'))
                ->setIcon('magnify')
                ->onClick(
                    (new ReloadById('renewalDataTable'))
                        ->withDataFromFormById($this->getId())
                )
        );

        $renewalPeriodField = new Dropdown();
        $renewalPeriodField->setTitle($this->translate('renewalPeriod'));
        $renewalPeriodField->setName('renewalPeriod');
        $renewalPeriodField->setDefaultValueAsFirstOption();
        $this->builder->addField($renewalPeriodField);

        $this->addElement($toolbar);
    }
}