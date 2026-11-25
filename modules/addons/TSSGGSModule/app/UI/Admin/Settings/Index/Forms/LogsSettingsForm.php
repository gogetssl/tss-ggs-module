<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Every log in one group, one switch each (values in App\Libs\Support\GeneralSettings), then "Debug one order":
 *   Certificate Log, Technical log (Logs page), Events, API requests - kept in the module
 *   WHMCS Module Log - shows YES only while WHMCS has its Module Log on too; YES switches it on in WHMCS
 *                      (GeneralSettingsProvider, App\Libs\Support\WhmcsModuleLog)
 *   WHMCS Activity Log - certificate events in the WHMCS activity log (WHMCS has no switch of its own for that log)
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

        $logs = $this->section('logs', 'text-box-outline');
        $this->builder->addFieldInContainer($logs, (new Switcher())->setName('certificateLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($logs, (new Switcher())->setName('internalLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($logs, (new Switcher())->setName('eventLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($logs, (new Dropdown())->setName('apiLogMode'), true);
        $this->builder->addFieldInContainer($logs, (new Switcher())->setName('moduleLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($logs, (new Switcher())->setName('whmcsActivityLog')->addClass('switcher-revert'), true);

        $debug = $this->section('debugLogs', 'bug');
        $this->builder->addFieldInContainer($debug, (new FormInputText())->setName('debugServices'), true);
        $this->builder->addFieldInContainer($debug, (new Dropdown())->setName('debugHours'), true);

        $this->saveBar();
    }
}
