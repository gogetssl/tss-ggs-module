<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\ApiSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class ApiSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected $configuration;

    public function __construct($configuration = false)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->provider       = ApiSettingsProvider::class;
        $this->providerAction = ApiSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('SettingsForm');
        $toolbar = new Toolbar();

        $btnName = $this->translate('save');
        if($this->configuration === true) $btnName = $this->translate('next_step');

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($btnName)
                ->onClick(new FormSubmit($this))
        );

        $toolbar->addElement(
            (new ButtonPrimary())
                ->setId('buttonTestConnection')
                ->setTitle($this->translate('testConnection'))
                ->onClick((new FormSubmit($this))->setCustomAction(ApiSettingsProvider::ACTION_TEST_CONNECTION))
        );

        $moduleConfiguration = (new AddonModuleRepository())->getModuleConfiguration();
        $selectVendors = $moduleConfiguration['vendors'];

        $rows = [];
        foreach ($selectVendors as $vendor)
        {
            $widget = new Widget();
            $widget->setTitle($this->translate($vendor));
            $rows[] = [$widget, 6];
        }

        $grid = new Grid();
        $grid->setRows([$rows]);
        $this->addElement($grid);

        foreach ($rows as $key => $row)
        {
            $this->builder->addFieldInContainer($row[0], (new FormInputText())->setName($selectVendors[$key].'LivePartnerCode'));
            $this->builder->addFieldInContainer($row[0], (new FormInputPassword())->setName($selectVendors[$key].'LiveAuthToken'));

            if($selectVendors[$key] == 'tss')
            {
                $this->builder->addFieldInContainer($row[0], (new FormInputText())->setName($selectVendors[$key].'TestPartnerCode'));
                $this->builder->addFieldInContainer($row[0], (new FormInputPassword())->setName($selectVendors[$key].'TestAuthToken'));
                $this->builder->addFieldInContainer($row[0], (new Dropdown())->setName('OperationMode')->setOptions(['live' => 'Live', 'sandbox' => 'Sandbox'])->setDefaultValueAsFirstOption());
            }

        }

        $this->addElement($toolbar);
    }
}