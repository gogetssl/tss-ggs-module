<?php

namespace ModulesGarden\TTSGGSModule\Components\FormInputGroupLabel;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

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
