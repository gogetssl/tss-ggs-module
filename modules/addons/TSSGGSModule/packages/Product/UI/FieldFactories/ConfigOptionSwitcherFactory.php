<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\FieldFactories;

use ModulesGarden\TSSGGSModule\Components\Form\Builder\Builders\SwitcherFactory;
use ModulesGarden\TSSGGSModule\Components\Span\Span;
use ModulesGarden\TSSGGSModule\Components\Tooltip\Tooltip;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\Formatters\ConfigOptionFullNameFormatter;

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