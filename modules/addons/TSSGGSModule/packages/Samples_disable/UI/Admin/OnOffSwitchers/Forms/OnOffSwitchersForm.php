<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\OnOffSwitchers\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\OnOffSwitchers\Providers\OnOffSwitchersProviders;

class OnOffSwitchersForm extends AbstractForm implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = OnOffSwitchersProviders::class;
    protected string $providerAction = OnOffSwitchersProviders::ACTION_UPDATE;

    public function __construct()
    {
        parent::__construct();

        $widget = new Widget();
        $this->addElement($widget);

        $this->builder = BuilderCreator::twoColumnsInContainer($this, $widget);
        $this->builder->createSubmitButton();
    }

    public function loadHtml(): void
    {
        $this->builder->createField(Switcher::class, 'switcherOnOff', true)
            ->setDefaultValue(Switcher::STATE_ON)
            ->setOnOffMode();
    }
}