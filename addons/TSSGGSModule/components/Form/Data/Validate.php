<?php

namespace ModulesGarden\TSSGGSModule\Components\Form\Data;

use function ModulesGarden\TSSGGSModule\Core\validator;

class Validate
{
    public function run(array $elements)
    {
        $validatableElementsBag = new ValidatableElementsBag($elements);

        $input = array_merge(
            \ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::get('formData', []),
            \ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::files()->get('formData', [])
        );

        validator()->validate(
            $input ?? [],
            $validatableElementsBag->getValidators(),
            $validatableElementsBag->getCustomAttributes(),
            $validatableElementsBag->getCustomValues()
        );
    }
}
