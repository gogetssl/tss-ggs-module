<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters\DataModels;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelInterface;

class Query extends Collection implements DataModelInterface
{
    public function __construct($query)
    {
        parent::__construct($query->get());
    }
}