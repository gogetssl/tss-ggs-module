<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Options;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\AbstractOption;

class SubTitleOptions extends TitleOptions
{
    public function __construct(string $text = '', string $align = 'center')
    {
        parent::__construct($text, $align);

        $this->style = ['fontSize' => "12px"];
    }
}