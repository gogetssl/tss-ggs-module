<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Builder\Builders;

use ModulesGarden\TTSGGSModule\Components\Span\Span;
use ModulesGarden\TTSGGSModule\Components\Tooltip\Tooltip;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

class SwitcherFactory extends AbstractFormFieldFactory
{
    public function create(FormFieldInterface $formField)//: FormFieldInterface
    {
        $span = new Span();
        $span->setCss('lu-form-text');
        $span->setContent($this->title);

        if (!empty($this->description))
        {
            $icon = new Tooltip();
            $icon->setTitle($this->description);
            $span->addElement($icon);
        }

        $formField->appendCss('lu-form-control');
        $formField->addElement($span);

        $formGroup = $this->createContainer();

        $formGroup->appendCss('lu-form-check lu-m-b-2x');
        $formGroup->addElement($formField);

        return $formGroup;
    }
}
