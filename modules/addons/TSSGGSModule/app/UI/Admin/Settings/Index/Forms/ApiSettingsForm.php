<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\ApiSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
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
        // a stored token is never sent back to the page (ApiSettingsProvider::read), so its field shows a mask as the
        // placeholder: the admin sees that a token is saved, and the placeholder is not a value - an empty field is still
        // submitted empty, which Save and Test connection read as "keep the stored token"
        $stored = $moduleConfiguration['credentials'] ?? [];
        $mask   = function($vendor, $field) use ($stored) {
            $input = (new FormInputPassword())->setName($field === 'TestAuthToken' ? $vendor . 'TestAuthToken' : $vendor . 'LiveAuthToken');
            if(trim((string)($stored[$vendor][$field] ?? '')) !== '')
            {
                $input->setPlaceholder('************');
            }

            return $input;
        };

        // the edition's own vendor, then (not in the setup wizard) the other vendor the administrator can connect:
        // a switch plus its credentials - only its exclusive products are offered then (App\Libs\CrossVendor)
        $vendors = [\ModulesGarden\TSSGGSModule\App\Libs\Edition::primaryVendor()];
        if($this->configuration !== true)
        {
            $vendors[] = \ModulesGarden\TSSGGSModule\App\Libs\Edition::otherVendor();
        }

        foreach($vendors as $vendor)
        {
            if($vendor === \ModulesGarden\TSSGGSModule\App\Libs\Edition::primaryVendor())
            {
                $widget = $this->section($vendor, 'key-outline');
            }
            else
            {
                $widget = $this->section('connect_' . $vendor, 'power-plug-outline');
                $this->builder->addFieldInContainer($widget, (new Switcher())->setName('connectOther')->addClass('switcher-revert'));
            }

            $this->builder->addFieldInContainer($widget, (new FormInputText())->setName($vendor . 'LivePartnerCode'));
            $this->builder->addFieldInContainer($widget, $mask($vendor, 'AuthToken'));

            if($vendor == 'tss')
            {
                $this->builder->addFieldInContainer($widget, (new FormInputText())->setName($vendor . 'TestPartnerCode'));
                $this->builder->addFieldInContainer($widget, $mask($vendor, 'TestAuthToken'));
                $this->builder->addFieldInContainer($widget, (new Dropdown())->setName('OperationMode')->setOptions(['live' => 'Live', 'sandbox' => 'Sandbox'])->setDefaultValueAsFirstOption());
            }
        }

        // with both vendors on the page the buttons work on both accounts while Connect is on: said above them (shown only
        // while the switch is on, resources/assets/js/custom/apiScope.js), and the result lists each API
        if(count($vendors) > 1)
        {
            $this->addElement((new \ModulesGarden\TSSGGSModule\Components\Text\Text())->setText(
                '<div class="tssggs-sec__desc tssggs-api-scope" data-switch="connectOther"'
                . (\ModulesGarden\TSSGGSModule\App\Libs\Edition::connectedVendor() === '' ? ' style="display:none"' : '') . '><p>' . htmlspecialchars($this->translate('buttonsScope', [
                    'primary' => \ModulesGarden\TSSGGSModule\App\Libs\Edition::brandOf($vendors[0]),
                    'other'   => \ModulesGarden\TSSGGSModule\App\Libs\Edition::brandOf($vendors[1]),
                ])) . '</p></div>'
            ));
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
