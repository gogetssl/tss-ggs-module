<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Data;

class ValidatableElementsBag
{
    protected array $validators = [];
    protected array $customAttributes = [];
    protected array $customValues = [];

    public function __construct($data)
    {
        $validators = [];
        $customAttributes = [];
        $customValues = [];

        $this->findValidatableElements($data, $validators, $customAttributes, $customValues);

        $this->validators = $validators;
        $this->customAttributes = $customAttributes;
        $this->customValues = $customValues;
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @return array
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    /**
     * @return array
     */
    public function getCustomValues(): array
    {
        return $this->customValues;
    }

    protected function findValidatableElements($data, &$validators = [], &$customAttributes = [], &$customValues = [])
    {
        if (is_object($data) && method_exists($data, 'getValidators'))
        {
            $validatorsList = $data->getValidators();

            if (!empty($validatorsList))
            {
                $validators[$data->getName()] = $validatorsList;

                if (method_exists($data, 'getTranslationCustomAttributes'))
                {
                    $customAttributes = $data->getTranslationCustomAttributes();
                }

                if (method_exists($data, 'getTranslationCustomValues'))
                {
                    $customValues = $data->getTranslationCustomValues();
                }
            }
        }

        if (is_object($data) && $slot = $data->getSlot('elements'))
        {
            $this->findValidatableElements($slot, $validators, $customAttributes, $customValues);
        }

        if (is_array($data))
        {
            foreach ($data as $row)
            {
                $this->findValidatableElements($row, $validators, $customAttributes, $customValues);
            }
        }
    }
}