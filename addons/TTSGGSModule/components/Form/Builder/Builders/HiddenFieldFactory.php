<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Builder\Builders;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

class HiddenFieldFactory extends AbstractFormFieldFactory
{
    public function create(FormFieldInterface $formField)//: FormFieldInterface
    {
        return $formField;
    }
}
