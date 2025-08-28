<?php

namespace ModulesGarden\TTSGGSModule\Components\ConfigurationVendorSelect;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\OptionsTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AvailableOptionsInterface;

class ConfigurationVendorSelect extends FormField implements AvailableOptionsInterface
{
    use OptionsTrait;

    public const COMPONENT = 'ConfigurationVendorSelect';
}
