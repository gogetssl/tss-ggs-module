<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait ValidatorTrait
{
    use ValidatorTranslationsTrait;

    protected $validators = [];

    public function clearValidators(): self
    {
        $this->validators = [];

        return $this;
    }

    public function getValidators(): array
    {
        return $this->validators;
    }

    public function setValidators(array $validators)
    {
        foreach ($validators as $validator)
        {
            $this->addValidator($validator);
        }

        return $this;
    }

    public function addValidator($validator): self
    {
        $this->validators[] = $validator;

        return $this;
    }
}
