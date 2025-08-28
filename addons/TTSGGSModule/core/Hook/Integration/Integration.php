<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook\Integration;

use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Enums\IntegrationInsertTypes;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Enums\IntegrationTypes;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Models\ControllerCallback;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\Models\RelatedField;

class Integration
{
    protected string $placement;
    protected string $requiredFile = "";
    protected string $jsFunctionName = "";
    protected array $requiredRequestFields = [];
    protected array $requiredPostFields = [];
    protected array $requiredGetFields = [];

    protected ControllerCallback $controllerCallback;
    protected IntegrationTypes $type;
    protected IntegrationInsertTypes $insertType = IntegrationInsertTypes::Full;
    protected RelatedField $relatedField;

    public function __construct(ControllerCallback $controllerCallback, string $selector)
    {
        $this->controllerCallback = $controllerCallback;
        $this->placement = $selector;
    }

    public function getControllerCallback():ControllerCallback
    {
        return $this->controllerCallback;
    }

    public function getPlacementSelector():string
    {
        return $this->placement;
    }

    public function requireFile(string $file):self
    {
        $this->requiredFile = $file;

        return $this;
    }

    public function getRequiredFile():string
    {
        return $this->requiredFile;
    }

    public function setJsFunctionName(string $jsFunction):self
    {
        $this->jsFunctionName = $jsFunction;

        return $this;
    }

    public function getJsFunctionName():string
    {
        return $this->jsFunctionName;
    }

    public function requireRequestFields(array $fields):self
    {
        $this->requiredRequestFields = $fields;

        return $this;
    }

    public function getRequiredRequestFields():array
    {
        return $this->requiredRequestFields;
    }

    public function requirePostFields(array $fields):self
    {
        $this->requiredPostFields = $fields;

        return $this;
    }

    public function getRequiredPostFields():array
    {
        return $this->requiredPostFields;
    }

    public function requireGetFields(array $fields):self
    {
        $this->requiredGetFields = $fields;

        return $this;
    }

    public function getRequiredGetFields():array
    {
        return $this->requiredGetFields;
    }

    public function setType(IntegrationTypes $type):self
    {
        $this->type = $type;

        return $this;
    }

    public function getType():string
    {
        return $this->type->value;
    }

    public function setInsertType(IntegrationInsertTypes $type):self
    {
        $this->insertType = $type;

        return $this;
    }

    public function getInsertType():string
    {
        return $this->insertType->value;
    }

    public function dependentOnFormField(RelatedField $field):self
    {
        $this->relatedField = $field;

        return $this;
    }

    public function getRelatedFieldSelector():?string
    {
        return isset($this->relatedField) ? $this->relatedField->getSelector() : null;
    }

    public function getRelatedFieldValues():?string
    {
        return isset($this->relatedField) ? implode(',', $this->relatedField->getValues()) : null;
    }
}