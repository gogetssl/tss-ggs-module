<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

use Illuminate\Contracts\Support\Arrayable;

abstract class Input implements Arrayable
{
    protected $value = null;

    protected string $name;
    protected string $title = "";
    protected string $description = "";
    protected string $validationMessage = "";
    protected bool $disabled = false;


    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setValue($value): Input
    {
        $this->value = $value;

        return $this;
    }

    public function setDisabled(bool $disabled = true): Input
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function setTitle(string $title): Input
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(string $description): Input
    {
        $this->description = $description;

        return $this;
    }

    public function setValidationMessage(string $validationMessage): Input
    {
        $this->validationMessage = $validationMessage;

        return $this;
    }

    public function toArray():array
    {
        return [
            'name'              => $this->name,
            'title'             => $this->title,
            'description'       => $this->description,
            'value'             => $this->value,
            'disabled'          => $this->disabled,
            'validationMessage' => $this->validationMessage
        ];
    }
}