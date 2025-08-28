<?php

namespace ModulesGarden\TTSGGSModule\Components\FormInputText;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnChangeTrait;

/**
 * Class IconButton
 */
class FormInputText extends FormField
{
    public const COMPONENT = 'FormInputText';

    public function setType(string $type): self
    {
        $this->setSlot('type', $type);

        return $this;
    }
}
