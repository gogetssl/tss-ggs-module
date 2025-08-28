<?php

namespace ModulesGarden\TTSGGSModule\Components\DataTable;

use ModulesGarden\TTSGGSModule\Core\Data\ToArrayTrait;
use ModulesGarden\TTSGGSModule\Core\DataProviders\AbstractRecordsListDataProvider;

class Column extends \ModulesGarden\TTSGGSModule\Core\DataProviders\Column implements \JsonSerializable
{
    use ToArrayTrait;

    protected $filter;
    protected $id;
    protected $tableName = null;
    protected $title;

    /**
     * @var string[]
     */
    protected $toArray = [
        'id',
        'name',
        'title',
        'filter',
        'type',
        'sortable',
        'searchable',
    ];

    public function __construct(string $name, string $tableName = null)
    {
        $this->name  = $name;
        $this->id    = $name;
        $this->class = '';

        //will be taken from lang if possible
        $this->title     = $name;
        $this->tableName = $tableName;

        return $this;
    }

    public function getFullName($wrapped = true)
    {
        $vWrapp = $wrapped ? '`' : '';

        if ($this->tableName)
        {
            return $vWrapp . $this->tableName . $vWrapp . '.' . $vWrapp . $this->name . $vWrapp;
        }

        return $vWrapp . $this->name . $vWrapp;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param $isOrderable
     * @return $this
     * @deprecated use setSortable
     */
    public function setOrderable($isOrderable = true)
    {
        $this->setSortable(true);

        return $this;
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }
}