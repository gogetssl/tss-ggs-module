<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Builders;

use ModulesGarden\TTSGGSModule\Components\Container\ContainerColumn;
use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\Builder;
use ModulesGarden\TTSGGSModule\Components\FormGroup\FormGroup;
use ModulesGarden\TTSGGSModule\Components\FormGroup\FormGroupFullWidth;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\GroupBuilders\ConfigOptionSwitcherBuilder;

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