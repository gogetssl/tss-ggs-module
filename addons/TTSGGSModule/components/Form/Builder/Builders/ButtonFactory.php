<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Builder\Builders;

use ModulesGarden\TTSGGSModule\Components\Container\ContainerFullWidth;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

class ButtonFactory extends AbstractFormFieldFactory
{
    public function create(FormFieldInterface $formField)
    {
        $formGroup = new ContainerFullWidth();
        $formGroup->addElement($formField);

        return $formGroup;
    }
}
