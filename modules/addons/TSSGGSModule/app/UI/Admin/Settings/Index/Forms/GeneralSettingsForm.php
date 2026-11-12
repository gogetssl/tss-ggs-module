<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Client area, messages, logging and lifecycle settings (App\Libs\GeneralSettings)
 */
class GeneralSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    use SettingsLayout;

    public function __construct()
    {
        parent::__construct();

        $this->provider       = GeneralSettingsProvider::class;
        $this->providerAction = GeneralSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('GeneralSettingsForm');

        $clientArea = $this->section('clientArea', 'account-group-outline');
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('clientSummary')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($clientArea, (new Dropdown())->setName('sidebarTemplates')->setMultiple()->setAllowToCreate()->setPlaceholder($this->translate('sidebarTemplates_placeholder')), true);
        $this->builder->addFieldInContainer($clientArea, (new Dropdown())->setName('expiresSoonDays'), true);
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('certificatesByDomain')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('sslTools')->addClass('switcher-revert'), true);

        $interface = $this->section('interface', 'view-dashboard-outline');
        $this->builder->addFieldInContainer($interface, (new Switcher())->setName('hideInfoMessages')->addClass('switcher-revert'), true);

        $lifecycle = $this->section('lifecycle', 'sync');
        $this->builder->addFieldInContainer($lifecycle, (new Switcher())->setName('terminateCancelsOrder')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($lifecycle, (new Switcher())->setName('renewReuseOrderDetails')->addClass('switcher-revert'), true);
        // termination of unpaid services and the pricing switches are on Settings > Finance

        $this->saveBar();
    }
}
