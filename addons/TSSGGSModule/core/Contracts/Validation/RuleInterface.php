<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Validation;

interface RuleInterface extends \Illuminate\Contracts\Validation\Rule
{
    public function passes($attribute, $value);

    public function message();
}