<?php

namespace ModulesGarden\TTSGGSModule\Components\DatePicker;

use ModulesGarden\TTSGGSModule\Components\DatePicker\Enums\Format;
use ModulesGarden\TTSGGSModule\Components\DatePicker\Enums\Type;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;

abstract class AbstractPicker extends FormField
{
    public const COMPONENT = 'DatePicker';

    public function setFormat(Format $format):self
    {
        $this->setSlot('format', $format->value);

        return $this;
    }

    public function setType(Type $type):self
    {
        $this->setSlot('type', $type->value);

        return $this;
    }

    public function enableRange(bool $enableRange = true):self
    {
        $this->setSlot('range', $enableRange);

        return $this;
    }

    public function setEditable(bool $editable = true): self
    {
        $this->setSlot('editable', $editable);

        return $this;
    }

    public function setClearable(bool $clearable = true): self
    {
        $this->setSlot('clearable', $clearable);

        return $this;
    }

    public function enableConfirmButton(bool $confirmButton = true): self
    {
        $this->setSlot('confirmButtonEnabled', $confirmButton);

        return $this;
    }
}