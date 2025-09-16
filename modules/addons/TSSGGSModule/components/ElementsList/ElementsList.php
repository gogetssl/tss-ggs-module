<?php

namespace ModulesGarden\TSSGGSModule\Components\ElementsList;

use ModulesGarden\TSSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;

abstract class ElementsList extends DataTable
{
    public const COMPONENT = 'ElementsList';

    abstract protected function buildElement($record):AbstractComponent;

    protected function parseDataSetRecords(): void
    {
        foreach ($this->dataSet->getRecords() as $record)
        {
            $this->addElement($this->buildElement($record));
        }
    }
}