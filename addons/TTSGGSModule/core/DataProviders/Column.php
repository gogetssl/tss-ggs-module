<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

class Column
{
    /**
     *
     */
    public const TYPE_DATE = 'date';
    /**
     *
     */
    public const TYPE_INT = 'int';
    /**
     *
     */
    public const TYPE_STRING = 'string';
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $type = self::TYPE_STRING;
    /**
     * @var bool
     */
    protected bool $sortable = false;
    /**
     * @var bool
     */
    protected bool $searchable = false;

    public function __construct(string $name, string $type = self::TYPE_STRING, bool $searchable = false, bool $sortable = false)
    {
        $this->name       = $name;
        $this->type       = $type;
        $this->searchable = $searchable;
        $this->sortable   = $sortable;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * @param string $name
     * @return Column
     */
    public function setName(string $name): Column
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $type
     * @return Column
     */
    public function setType(string $type): Column
    {
        if (!in_array($type, [self::TYPE_DATE, self::TYPE_INT, self::TYPE_STRING]))
        {
            throw new \Exception('Invalid column type');
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @param bool $sortable
     * @return Column
     */
    public function setSortable(bool $sortable = true): Column
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @param bool $searchable
     * @return Column
     */
    public function setSearchable(bool $searchable = true, ?string $type = null): Column
    {
        if ($type)
        {
            $this->setType($type);
        }

        $this->searchable = $searchable;
        return $this;
    }
}