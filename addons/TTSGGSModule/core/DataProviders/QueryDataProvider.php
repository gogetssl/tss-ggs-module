<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use ModulesGarden\TTSGGSModule\Core\Contracts\DataSetInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\QueryProviderInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider\ColumnName;

class QueryDataProvider extends AbstractRecordsListDataProvider implements QueryProviderInterface
{
    use \ModulesGarden\TTSGGSModule\Core\Components\FallbackTraits\QueryDataProvider;

    private $fullDataLength = 0;
    private $query = null;

    public function __construct($query = null)
    {
        $this->setQuery($query);
    }

    public function setQuery($query): QueryProviderInterface
    {
        $this->query = $query;

        return $this;
    }

    public function getData()/*: \ModulesGarden\TTSGGSModule\Core\Contracts\DataSetInterface*/
    {
        $this->setupGlobalSearch();
        $this->setupFilersSearch();
        $this->getResults();
        $this->setupSorting();
        $this->countRawResults();
        $this->setupLimit();

        return $this->getResults();
    }

    protected function searchAsFilter($column, $query, string $searchFor):void
    {
        if (!$column->isSearchable())
        {
            return;
        }

        $query->where(
            DB::raw('LOWER(' . ColumnName::withTableName($column->getName()) . ')'),
            $this->getSearchType($column),
            [$this->getSearchWrapperPrefix($column) . strtolower($searchFor) . $this->getSearchWrapperSuffix($column)]
        );
    }

    protected function searchAsGlobal($column, $query, string $searchFor):void
    {
        if (!$column->isSearchable())
        {
            return;
        }

        $query->orWhere(
            DB::raw('LOWER(' . ColumnName::withTableName($column->getName()) . ')'),
            $this->getSearchType($column),
            [$this->getSearchWrapperPrefix($column) . strtolower($searchFor) . $this->getSearchWrapperSuffix($column)]
        );
    }

    protected function getSearchWrapperPrefix(Column $column):string
    {
        return $column->getType() === Column::TYPE_INT ? '' : '%';
    }

    protected function getSearchWrapperSuffix(Column $column):string
    {
        return $column->getType() === Column::TYPE_INT ? '' : '%';
    }

    protected function getSearchType(Column $column):string
    {
        return $column->getType() === Column::TYPE_INT ? '=' : 'LIKE';
    }

    protected function setupFilersSearch():self
    {
        if (empty($this->filterFields))
        {
            return $this;
        }

        $this->query->where(function($query) {
            foreach ($this->filterFields as $field => $searchFor)
            {
                if (empty($searchFor) && !is_numeric($searchFor))
                {
                    continue;
                }

                $column = current(array_filter($this->columns, function(Column $column) use ($field) {
                    return $column->getName() === $field;
                }));

                if (!$column)
                {
                    continue;
                }

                $this->searchAsFilter($column, $query, $searchFor);
            }
        });

        return $this;
    }

    protected function setupGlobalSearch():self
    {
        if (!$this->searchFor)
        {
            return $this;
        }

        $this->query->where(function($query) {
            /**
             * @var $column Column
             */
            foreach ($this->columns as $column)
            {
                $this->searchAsGlobal($column, $query, $this->searchFor);
            }
        });

        return $this;
    }

    protected function getResults(): DataSetInterface
    {
        return new DataSet(
            $this->query->get(),
            $this->offset,
            $this->fullDataLength,
            $this->orderBy,
            $this->orderDir
        );
    }

    protected function setupSorting()
    {
        if (!$this->orderBy || !$this->orderDir)
        {
            return;
        }

        foreach ($this->columns as $column)
        {
            /**
             * @var $column Column
             */
            if (ColumnName::onlyName($this->orderBy) === ColumnName::onlyName($column->getName()))
            {
                $this->query->orderBy(ColumnName::withTableName($column->getName()), $this->orderDir);

                return;
            }
        }

        throw new \Exception('Invalid sort column ' . $this->orderBy);
    }

    public function countRawResults()
    {
        //
        $this->fullDataLength = $this->query instanceof Builder ? $this->query->getCountForPagination() : $this->query->getQuery()->getCountForPagination();
    }

    protected function setupLimit()
    {
        $this->query->offset($this->offset);
        $this->query->limit($this->limit);
    }
}
