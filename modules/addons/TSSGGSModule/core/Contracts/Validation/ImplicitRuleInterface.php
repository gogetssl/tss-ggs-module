<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Validation;

interface ImplicitRuleInterface extends \Illuminate\Contracts\Validation\ImplicitRule
{
    public function passes($attribute, $value);

    public function message();
}