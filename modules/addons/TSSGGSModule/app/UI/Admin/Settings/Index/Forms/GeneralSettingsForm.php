<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Checkbox\Checkbox;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * Client area, messages, logging and lifecycle settings (App\Libs\GeneralSettings)
 */
class GeneralSettingsForm extends Form implements AdminAreaInterface, AjaxComponentInterface
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
        $this->setId('GeneralSettingsForm');

        $left  = new Container();
        $right = new Container();
        $grid  = new Grid();
        $grid->setRows([[[$left, 6], [$right, 6]]]);
        $this->addElement($grid);

        $clientArea = $this->widget($left, 'clientArea');
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('clientSummary')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($clientArea, (new FormInputText())->setName('sidebarTemplates'), true);
        $this->builder->addFieldInContainer($clientArea, (new Dropdown())->setName('expiresSoonDays'), true);
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('certificatesByDomain')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($clientArea, (new Switcher())->setName('sslTools')->addClass('switcher-revert'), true);

        $logging = $this->widget($left, 'logging');
        $this->builder->addFieldInContainer($logging, (new Switcher())->setName('whmcsActivityLog')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($logging, (new Switcher())->setName('moduleLog')->addClass('switcher-revert'), true);

        $lifecycle = $this->widget($left, 'lifecycle');
        $this->builder->addFieldInContainer($lifecycle, (new Switcher())->setName('terminateCancelsOrder')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($lifecycle, (new Switcher())->setName('terminateUnpaidOnExpiry')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($lifecycle, (new FormInputText())->setName('defaultWebServer'), true);
        $this->builder->addFieldInContainer($lifecycle, (new Dropdown())->setName('dueDateOffsetRegular'), true);
        $this->builder->addFieldInContainer($lifecycle, (new Dropdown())->setName('dueDateOffsetSubscription'), true);

        // formats the client can download (a checkbox each)
        $downloads = $this->widget($right, 'downloads');
        foreach(['zip', 'crt', 'ca', 'fullchain', 'nginx', 'apache', 'pkcs7'] as $format)
        {
            $this->builder->addFieldInContainer($downloads, (new Switcher())->setName('downloadFormat_' . $format)->addClass('switcher-revert'), true);
        }

        $pricing = $this->widget($left, 'pricing');
        $this->builder->addFieldInContainer($pricing, (new Switcher())->setName('keepMarginOnSync')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($pricing, (new Switcher())->setName('belowCostEmail')->addClass('switcher-revert'), true);

        $messages = $this->widget($right, 'messages');
        $this->builder->addFieldInContainer($messages, (new TextArea())->setName('processingGuide'), true);
        $this->builder->addFieldInContainer($messages, (new TextArea())->setName('issuedMessage'), true);
        $this->builder->addFieldInContainer($messages, (new Dropdown())->setName('certificateEmailTemplate'), true);

        $agreement = $this->widget($right, 'agreement');
        $this->builder->addFieldInContainer($agreement, (new Switcher())->setName('requireAgreement')->addClass('switcher-revert'), true);
        $this->builder->addFieldInContainer($agreement, (new TextArea())->setName('agreementText'), true);

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
}
