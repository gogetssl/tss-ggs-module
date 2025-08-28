<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Forms;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Collapse;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Providers\CollapsableBoxProvider;

class CollapsableDropdownForm extends Form implements AjaxComponentInterface, AdminAreaInterface
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

        $triggerDropdown = new Dropdown();
        $triggerDropdown->setOptions([
           'opcja1' => "Opcja1",
           'opcja2' => "Opcja2",
           'opcja3' => "Opcja3",
           'opcja4' => "Opcja4"
        ]);

        $collapseAction = new Collapse($container, Collapse::DEFAULT_COLLAPSED);
        $collapseAction->visibleOnValues(["opcja3", "opcja4"]);

        $triggerDropdown->onChange($collapseAction);

        $this->builder->addField($triggerDropdown);
        $this->builder->addElement($container);
    }
}