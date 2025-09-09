<?php

namespace ModulesGarden\TSSGGSModule\Components\Form\Builder\Builders;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;

class HiddenFieldFactory extends AbstractFormFieldFactory
{
    public function create(FormFieldInterface $formField)//: FormFieldInterface
    {
        return $formField;
    }
}
