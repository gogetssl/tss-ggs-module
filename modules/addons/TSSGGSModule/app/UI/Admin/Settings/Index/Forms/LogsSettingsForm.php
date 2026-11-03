<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Every log the module offers, one switch each. The values live in the general settings (App\Libs\GeneralSettings).
 *   In the module: Certificate Log (page) and Module Log (Logs page)
 *   In WHMCS:      Activity Log and Module Log (Utilities > Logs)
 */
class LogsSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
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
        $this->setId('LogsSettingsForm');

        $module = $this->section('moduleLogs', 'text-box-outline');
        $this->builder->addFieldInContainer($module, (new Switcher())->setName('certificateLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($module, (new Switcher())->setName('internalLog')->addClass('switcher-revert'), true);

        $whmcs = $this->section('whmcsLogs', 'clipboard-text');
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('whmcsActivityLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('moduleLog')->addClass('switcher-revert'), true);

        $this->saveBar();
    }
}
