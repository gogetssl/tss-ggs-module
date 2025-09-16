<?php

namespace ModulesGarden\TSSGGSModule\Core\Exporters\DataModels;

use ModulesGarden\TSSGGSModule\Core\Exporters\Source\DataModelInterface;

class Query extends Collection implements DataModelInterface
{
    public function __construct($query)
    {
        parent::__construct($query->get());
    }
}