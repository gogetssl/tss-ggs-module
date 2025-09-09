<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputText;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnChangeTrait;

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
