<?php

namespace ModulesGarden\TTSGGSModule\Components\HiddenField;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldHiddenInterface;

class HiddenField extends FormField implements FormFieldHiddenInterface
{
    public const COMPONENT = 'HiddenField';
}
