<?php

namespace ModulesGarden\TSSGGSModule\Components\HiddenField;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldHiddenInterface;

class HiddenField extends FormField implements FormFieldHiddenInterface
{
    public const COMPONENT = 'HiddenField';
}
