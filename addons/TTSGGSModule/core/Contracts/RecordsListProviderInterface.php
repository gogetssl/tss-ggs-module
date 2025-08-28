<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts;

interface RecordsListProviderInterface
{
    public function getData()/*: DataSetInterface*/ ;

    public function setDefaultSorting(string $column, string $direction): self;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;

    public function setSearch(string $toSearch): self;

    public function setFieldSearch(string $field, string $toSearch): self;

    public function setSortBy(string $colName): self;

    public function setSortDir(string $sortDir): self;

    public function setColumns(array $columns): self;

    public function getColumns(): array;
}
