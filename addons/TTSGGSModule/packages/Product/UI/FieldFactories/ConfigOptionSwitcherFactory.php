<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\FieldFactories;

use ModulesGarden\TTSGGSModule\Components\Form\Builder\Builders\SwitcherFactory;
use ModulesGarden\TTSGGSModule\Components\Span\Span;
use ModulesGarden\TTSGGSModule\Components\Tooltip\Tooltip;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\Formatters\ConfigOptionFullNameFormatter;

class ConfigOptionSwitcherFactory extends SwitcherFactory
{
    public function create(FormFieldInterface $formField)
    {
        $span = new Span();
        $span->setCss('lu-form-text');
        $span->addElement(ConfigOptionFullNameFormatter::buildFullNameContainer($this->title));

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