<?php

namespace ModulesGarden\TTSGGSModule\Components\TableSimple;

use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;

class TableSimple extends AbstractComponent
{
    use AjaxTrait;
    use CssContainerTrait;

    public const COMPONENT = 'TableSimple';

    public function addColumn(Column\Column $column): self
    {
        $this->pushToSlot('columns', $column->toString());

        return $this;
    }

    public function addRecord(Record $record): self
    {
        $this->pushToSlot('records', $record->toArray());

        return $this;
    }

    public function setColumns(array $arrayOfColumns): self
    {
        foreach ($arrayOfColumns as $column)
        {
            $this->addColumn($column);
        }

        return $this;
    }

    public function setRecords(array $arrayOfRecords): self
    {
        $this->setSlot('records', $arrayOfRecords);

        return $this;
    }

    public function setTextCentered(bool $textCentered = true): self
    {
        $this->setSlot('textCentered', $textCentered);

        return $this;
    }
}
