<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputGroupLabel;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;

/**
 * Class Form
 */
class FormInputGroupLabel extends AbstractComponent implements FormFieldInterface
{
    public const COMPONENT = 'FormInputGroupLabel';

    public function getName(): string
    {
        return '';
    }

    public function setText(string $text): self
    {
        $this->setSlot('text', $text);

        return $this;
    }
}
