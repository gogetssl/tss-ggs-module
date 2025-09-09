<?php

namespace ModulesGarden\TSSGGSModule\Components\RadioButton;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\OptionsTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AvailableOptionsInterface;

class RadioButton extends FormField implements AvailableOptionsInterface
{
    use OptionsTrait;

    public const COMPONENT = 'RadioButton';
}
