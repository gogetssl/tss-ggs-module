<?php

namespace ModulesGarden\TTSGGSModule\Components\ElementsList;

use ModulesGarden\TTSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

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