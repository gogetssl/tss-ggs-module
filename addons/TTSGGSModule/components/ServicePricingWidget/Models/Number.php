<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

class Number extends Input
{
    public function __construct(string $name = "", float $value = 0, string $title = '', string $description = '')
    {
        parent::__construct($name);

        $this->setValue($value);
        $this->setTitle($title);
        $this->setDescription($description);
    }
}