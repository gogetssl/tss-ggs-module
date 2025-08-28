<?php

namespace ModulesGarden\TTSGGSModule\Components\RadioButton;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\OptionsTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AvailableOptionsInterface;

class RadioButton extends FormField implements AvailableOptionsInterface
{
    use OptionsTrait;

    public const COMPONENT = 'RadioButton';
}
