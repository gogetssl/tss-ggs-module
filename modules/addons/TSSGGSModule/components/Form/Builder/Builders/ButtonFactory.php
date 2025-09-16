<?php

namespace ModulesGarden\TSSGGSModule\Components\Form\Builder\Builders;

use ModulesGarden\TSSGGSModule\Components\Container\ContainerFullWidth;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;

class ButtonFactory extends AbstractFormFieldFactory
{
    public function create(FormFieldInterface $formField)
    {
        $formGroup = new ContainerFullWidth();
        $formGroup->addElement($formField);

        return $formGroup;
    }
}
