<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use ModulesGarden\TTSGGSModule\Core\Contracts\RecordsListProviderInterface;

abstract class AbstractRecordsListDataProvider implements RecordsListProviderInterface
{
    public const SORT_ASC  = 'ASC';
    public const SORT_DESC = 'DESC';

    /**
     * @var array
     */
    protected array $columns = [];
    /**
     * @var array
     */
    protected int $limit = 10;
    protected int $offset = 0;
    protected $orderBy = '';
    protected $orderDir = self::SORT_ASC;
    protected string $searchFor;

    protected array $filterFields = [];


    public function setDefaultSorting(string $column, string $direction): RecordsListProviderInterface
    {
        $this->setSortBy($column);
        $this->setSortDir($direction);

        return $this;
    }

    public function setSortBy(string $colName): RecordsListProviderInterface
    {
        $this->orderBy = $colName;

        return $this;
    }

    public function setFieldSearch(string $field, string $toSearch): RecordsListProviderInterface
    {
        $this->filterFields[$field] = $toSearch;

        return $this;
    }

    public function setSortDir(string $sortDir): RecordsListProviderInterface
    {
        if (!in_array($sortDir, [self::SORT_ASC, self::SORT_DESC]))
        {
            throw new \Exception('Invalid sortDir');
        }

        $this->orderDir = $sortDir;

        return $this;
    }

    public function setLimit(int $limit): RecordsListProviderInterface
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset): RecordsListProviderInterface
    {
        $this->offset = $offset;

        return $this;
    }

    public function setSearch(string $toSearch): RecordsListProviderInterface
    {
        $this->searchFor = $toSearch;

        return $this;
    }

    public function setColumns(array $columns): RecordsListProviderInterface
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
