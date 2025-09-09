<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Forms;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Collapse;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Providers\CollapsableBoxProvider;


class CollapsableForm extends Form implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = CollapsableBoxProvider::class;
    protected string $providerAction = CollapsableBoxProvider::ACTION_UPDATE;

    public function __construct()
    {
        parent::__construct();

        $this->builder = BuilderCreator::twoColumns($this);
        $this->builder->createSubmitButton();
    }

    public function loadHtml(): void
    {
        $container = new Container();


        $this->builder->createFieldInContainer($container, Switcher::class, 'switcher1', true);
        $this->builder->createFieldInContainer($container, Switcher::class, 'switcher2', true);
        $this->builder->createFieldInContainer($container, Switcher::class, 'switcher3', true);
        $this->builder->createFieldInContainer($container, Switcher::class, 'switcher4', true);
        $this->builder->createFieldInContainer($container, FormInputText::class, 'someInput', true);
        $this->builder->createFieldInContainer($container, FormInputText::class, 'someInput2', true);
        $this->builder->createFieldInContainer($container, FormInputText::class, 'someInput3', true);
        $this->builder->createFieldInContainer($container, FormInputText::class, 'someInput4', true);

        $triggerSwitcher = new Switcher();
        $triggerSwitcher->onChange(new Collapse($container, Collapse::DEFAULT_COLLAPSED));

        $this->builder->addField($triggerSwitcher);
        $this->builder->addElement($container);
    }
}