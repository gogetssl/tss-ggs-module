<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
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
    use SettingsLayout;

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

        $moduleConfiguration = (new AddonModuleRepository())->getModuleConfiguration();

        foreach($moduleConfiguration['vendors'] as $vendor)
        {
            $widget = $this->section($vendor, 'key-outline');

            $this->builder->addFieldInContainer($widget, (new FormInputText())->setName($vendor . 'LivePartnerCode'));
            $this->builder->addFieldInContainer($widget, (new FormInputPassword())->setName($vendor . 'LiveAuthToken'));

            if($vendor == 'tss')
            {
                $this->builder->addFieldInContainer($widget, (new FormInputText())->setName($vendor . 'TestPartnerCode'));
                $this->builder->addFieldInContainer($widget, (new FormInputPassword())->setName($vendor . 'TestAuthToken'));
                $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('OperationMode')->setOptions(['live' => 'Live', 'sandbox' => 'Sandbox'])->setDefaultValueAsFirstOption());
            }
        }

        // Saving tests the connection first and the credentials may be prefilled from the legacy modules (not stored yet),
        // so Save stays enabled on this screen
        $this->saveBar(
            $this->configuration === true ? $this->translate('next_step') : null,
            true,
            [
                (new ButtonPrimary())
                    ->setId('buttonTestConnection')
                    ->setTitle($this->translate('testConnection'))
                    ->onClick((new FormSubmit($this))->setCustomAction(ApiSettingsProvider::ACTION_TEST_CONNECTION)),
            ]
        );
    }
}
