<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

use Illuminate\Contracts\Support\Arrayable;

class PriceLabel implements Arrayable
{
    protected string $text = "";
    protected string $description = "";
    protected array $css = [];

    public function __construct(string $text = "", string $description = "", array $css = [])
    {
        $this->text         = $text;
        $this->description  = $description;
        $this->css          = $css;
    }

    public function toArray():array
    {
        return [
            'text'          => $this->text,
            'description'   => $this->description,
            'css'           => $this->css
        ];
    }
}