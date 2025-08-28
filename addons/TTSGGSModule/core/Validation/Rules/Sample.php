<?php

namespace ModulesGarden\TTSGGSModule\Core\Validation\Rules;

use ModulesGarden\TTSGGSModule\Core\Contracts\Validation\ImplicitRuleInterface;

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