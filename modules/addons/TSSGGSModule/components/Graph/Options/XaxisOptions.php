<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Options;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\AbstractOption;

class XaxisOptions extends AbstractOption
{
    public $categories;

    public function __construct($categories = [])
    {
        $this->categories = $categories;
    }

    public function getAttributes():array
    {
        return [
            'categories' => $this->categories,
        ];
    }
}