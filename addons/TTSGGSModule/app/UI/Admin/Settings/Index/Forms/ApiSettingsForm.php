<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers\ApiSettingsProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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