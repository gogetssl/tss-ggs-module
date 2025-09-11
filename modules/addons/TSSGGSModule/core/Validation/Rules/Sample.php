<?php

namespace ModulesGarden\TSSGGSModule\Core\Validation\Rules;

use ModulesGarden\TSSGGSModule\Core\Contracts\Validation\ImplicitRuleInterface;

class Sample implements ImplicitRuleInterface
{
    public function passes($attribute, $value)
    {
        return false;
    }

    public function message()
    {
        return '';
    }
}