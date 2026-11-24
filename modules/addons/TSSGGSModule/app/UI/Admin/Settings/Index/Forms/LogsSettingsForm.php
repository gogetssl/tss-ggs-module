<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\Libs\WhmcsModuleLog;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns\SettingsLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
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
        $this->builder->addFieldInContainer($module, (new Dropdown())->setName('apiLogMode'), true);
        $this->builder->addFieldInContainer($module, (new Switcher())->setName('eventLog')->addClass('switcher-revert'), true);

        $debug = $this->section('debugLogs', 'bug');
        $this->builder->addFieldInContainer($debug, (new FormInputText())->setName('debugServices'), true);
        $this->builder->addFieldInContainer($debug, (new Dropdown())->setName('debugHours'), true);

        $whmcs = $this->section('whmcsLogs', 'clipboard-text');
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('whmcsActivityLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('moduleLog')->addClass('switcher-revert'), true);
        // the result of the "Turn on in WHMCS" button, once
        $result = $_SESSION[WhmcsModuleLog::SESSION] ?? null;
        unset($_SESSION[WhmcsModuleLog::SESSION]);
        if(!empty($result['ok']))
        {
            $whmcs->addElement((new Text())->setText('<div class="tssggs-note is-ok">' . htmlspecialchars($this->translate('whmcsModuleLogEnabled')) . '</div>'));
        }
        elseif(!empty($result['error']))
        {
            $whmcs->addElement((new Text())->setText('<div class="tssggs-note is-bad">' . htmlspecialchars((string)$result['error']) . '</div>'));
        }

        if(!WhmcsModuleLog::enabled())
        {
            // the switch above only says what the module sends; WHMCS drops it all while its own Module Log is off.
            // The button switches it on in WHMCS (WHMCS-wide, so only when the administrator asks for it)
            $button = '';
            if(WhmcsModuleLog::canEnable())
            {
                // this field set sits inside the settings form, where a form of its own is not allowed: the button builds
                // and posts one
                $token  = function_exists('generate_token') ? \generate_token('plain') : '';
                $button = ' <button type="button" class="lu-btn lu-btn--sm lu-btn--success" style="margin-left:8px" data-token="' . htmlspecialchars($token) . '"'
                    . ' onclick="this.disabled=true;var f=document.createElement(\'form\');f.method=\'post\';'
                    . 'f.action=\'addonmodules.php?module=TSSGGSModule&amp;mg-page=settings&amp;mg-action=enableWhmcsModuleLog\';'
                    . 'var i=document.createElement(\'input\');i.type=\'hidden\';i.name=\'token\';i.value=this.dataset.token;f.appendChild(i);document.body.appendChild(f);f.submit();">'
                    . htmlspecialchars($this->translate('whmcsModuleLogEnable')) . '</button>';
            }
            $whmcs->addElement((new Text())->setText('<div class="tssggs-renewal-check"><p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--amber">'
                . htmlspecialchars($this->translate('whmcsModuleLogOff')) . ' <a href="' . htmlspecialchars(WhmcsModuleLog::pageUrl()) . '">' . htmlspecialchars($this->translate('whmcsModuleLogOpen')) . '</a>'
                . $button . '</p></div>'));
        }

        $this->saveBar();
    }
}
