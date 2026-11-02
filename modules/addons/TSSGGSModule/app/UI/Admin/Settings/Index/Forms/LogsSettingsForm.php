<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Every log the module offers, one switch each. The values live in the general settings (App\Libs\GeneralSettings).
 *   In the module: Certificate Log (page) and Module Log (Logs page)
 *   In WHMCS:      Activity Log and Module Log (Utilities > Logs)
 */
class LogsSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
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

        $left  = new Container();
        $right = new Container();
        $grid  = new Grid();
        $grid->setRows([[[$left, 6], [$right, 6]]]);
        $this->addElement($grid);

        $module = $this->widget($left, 'moduleLogs');
        $this->about($module, 'moduleLogs_about');
        $this->builder->addFieldInContainer($module, (new Switcher())->setName('certificateLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($module, (new Switcher())->setName('internalLog')->addClass('switcher-revert'), true);

        $whmcs = $this->widget($right, 'whmcsLogs');
        $this->about($whmcs, 'whmcsLogs_about');
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('whmcsActivityLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($whmcs, (new Switcher())->setName('moduleLog')->addClass('switcher-revert'), true);

        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('save'))
                ->onClick(new FormSubmit($this))
        );
        $this->addElement($toolbar);
    }

    protected function widget(Container $container, $title)
    {
        $widget = new Widget();
        $widget->setTitle($this->translate($title));
        $container->addElement($widget);

        return $widget;
    }

    /** A short explanation under a group's heading */
    protected function about(Widget $widget, $key)
    {
        $widget->addElement((new Text())->setText('<p class="tssggs-logs-about">' . Icons::svg('information-outline') . '<span>' . htmlspecialchars($this->translate($key)) . '</span></p>'));
    }
}
