<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Data;

use function ModulesGarden\TTSGGSModule\Core\validator;

class Validate
{
    public function run(array $elements)
    {
        $validatableElementsBag = new ValidatableElementsBag($elements);

        $input = array_merge(
            \ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::get('formData', []),
            \ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::files()->get('formData', [])
        );

        validator()->validate(
            $input ?? [],
            $validatableElementsBag->getValidators(),
            $validatableElementsBag->getCustomAttributes(),
            $validatableElementsBag->getCustomValues()
        );
    }
}
