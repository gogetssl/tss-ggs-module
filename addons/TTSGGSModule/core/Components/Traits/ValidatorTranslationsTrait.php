<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use function ModulesGarden\TTSGGSModule\Core\translator;

trait ValidatorTranslationsTrait
{
    protected $customAttributes = [];
    protected $customValues = [];

    public function getTranslationCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public function setTranslationCustomAttributes(array $customAttributes, ?AbstractComponent $element = null)
    {
        foreach ($customAttributes as $customAttribute)
        {
            $this->addTranslationCustomAttribute($customAttribute, $element);
        }

        return $this;
    }

    public function addTranslationCustomAttribute(string $customAttribute, ?AbstractComponent $element = null): self
    {
        $element ?
            $this->customAttributes[$customAttribute] = translator()->getBasedOnNamespace(
                (new \ReflectionClass($element))->getNamespaceName(), $this->getName() . ".customAttributes." . $customAttribute
            ):
            $this->customAttributes[$customAttribute] = translator()->get("validation.customAttributes." . $customAttribute);

        return $this;
    }

    public function getTranslationCustomValues(): array
    {
        return $this->customValues;
    }

    public function setTranslationCustomValues(array $customValues, ?AbstractComponent $element = null)
    {
        foreach ($customValues as $attribute => $attributeValues)
        {
            $this->addTranslationCustomValue($attribute, $attributeValues, $element);
        }

        return $this;
    }

    public function addTranslationCustomValue(string $attribute, array $attributeValues, ?AbstractComponent $element = null): self
    {
        foreach ($attributeValues as $value)
        {
            $element ?
                $this->customValues[$attribute][$value] =translator()->getBasedOnNamespace(
                    (new \ReflectionClass($element))->getNamespaceName(), $this->getName() . ".customValues." . $attribute . "." . $value
                ):
                $this->customValues[$attribute][$value] = translator()->get("validation.customValues." . $value);
        }

        return $this;
    }
}