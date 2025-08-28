<?php

namespace ModulesGarden\TTSGGSModule\Core\Validation;

use ModulesGarden\TTSGGSModule\Core\Support\Bridges\IlluminateTranslator;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator;

class ValidatorService
{
    /**
     * @var \Illuminate\Validation\Validator
     */
    protected \Illuminate\Validation\Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator(new IlluminateTranslator(Translator::getFacadeRoot()), [], []);
    }

    public function errors()
    {
        return $this->validator->errors();
    }

    public function validate(array $data, array $rules, array $customAttributes = [], array $customValues = [])
    {
        //$this->registerCustomValidators($rules);
        $this->runValidator($data, $rules, $customAttributes, $customValues);
    }

    protected function runValidator(array $data, array $rules,  array $customAttributes = [], array $customValues = [])
    {
        return $this->validator
            ->setData($data)
            ->setRules($rules)
            ->setAttributeNames($customAttributes)
            ->setValueNames($customValues)
            ->validate();
    }

}
