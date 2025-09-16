<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\Builders;

use ModulesGarden\TSSGGSModule\Components\Container\ContainerColumn;
use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\Builder;
use ModulesGarden\TSSGGSModule\Components\FormGroup\FormGroup;
use ModulesGarden\TSSGGSModule\Components\FormGroup\FormGroupFullWidth;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\GroupBuilders\ConfigOptionSwitcherBuilder;

class ConfigurableOptionsBuilder extends Builder
{
    public function __construct(AbstractForm $form)
    {
        parent:: __construct($form);
        $this->setDefaultFormGroup(new FormGroupFullWidth());
        $this->addDefaultContainer(new ContainerColumn());
    }

    public function createGroup(FormFieldInterface $field, bool $showTooltip = true, FormGroup $formGroup = null)
    {
        return (new ConfigOptionSwitcherBuilder($this, $this->form, $this->defaultFormGroup))->build($field, $showTooltip, $formGroup);
    }
}