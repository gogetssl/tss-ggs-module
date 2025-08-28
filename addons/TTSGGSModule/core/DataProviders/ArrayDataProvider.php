<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use Illuminate\Support\Collection;
use ModulesGarden\TTSGGSModule\Core\Contracts\DataSetInterface;

class ArrayDataProvider extends AbstractRecordsListDataProvider //implements QueryProviderInterface
{
    private $collection = null;
    private $fullDataLength = 0;

    public function __construct($records = [])
    {
        $this->setData($records);
    }

    public function setData($records)//:// QueryProviderInterface
    {
        $this->collection = new \Illuminate\Support\Collection($records);

        return $this;
    }

    public function getData()/*: \ModulesGarden\TTSGGSModule\Core\Contracts\DataSetInterface*/
    {
        $this->avalibleCols = [];

        $this->setupGlobalSearch();
        $this->setupFilersSearch();
        $this->setupSorting();
        $this->countRawResults();
        $this->setupLimit();

        return $this->getResults();
    }

    protected function setupGlobalSearch(): self
    {
        if (!$this->searchFor)
        {
            return $this;
        }

        $this->collection = $this->collection->filter(function($item, $key) {
            foreach ($this->columns as $column)
            {
                if (!$column->isSearchable())
                {
                    continue;
                }

                if ($column->getType() === Column::TYPE_INT && is_numeric($this->searchFor) && (int)$item[$column->getName()] === (int)$this->searchFor)
                {
                    return true;
                }
                elseif ($column->getType() !== Column::TYPE_INT && stripos($item[$column->getName()], $this->searchFor) !== false)
                {
                    return true;
                }
            }

            return false;
        });

        return $this;
    }

    protected function setupFilersSearch(): self
    {
        if (empty($this->filterFields))
        {
            return $this;
        }

        $this->collection = $this->collection->filter(function($item, $key) {
            foreach ($this->filterFields as $field => $searchFor)
            {
                if (empty($searchFor) && !is_numeric($searchFor))
                {
                    continue;
                }

                $column = current(array_filter($this->columns, function(Column $column) use ($field) {
                    return $column->isSearchable() && $column->getName() === $field;
                }));

                if (!$column)
                {
                    continue;
                }

                if ($column->getType() === Column::TYPE_INT && is_numeric($searchFor) && (int)$item[$column->getName()] !== (int)$searchFor)
                {
                    return false;
                }
                elseif ($column->getType() !== Column::TYPE_INT && stripos($item[$column->getName()], $searchFor) === false)
                {
                    return false;
                }
            }

            return true;
        });

        return $this;
    }

    protected function getResults(): DataSetInterface
    {
        return new DataSet(
            $this->collection ?? [],
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
            if ($this->orderBy === $column->getName())
            {
                if ($column->getType() === Column::TYPE_INT)
                {
                    $this->collection = $this->orderDir === self::SORT_ASC ? $this->collection->sortBy($column->getName(), SORT_NUMERIC) : $this->collection->sortByDesc($column->getName(), SORT_NUMERIC);
                }
                else
                {
                    $this->collection = $this->orderDir === self::SORT_ASC ? $this->collection->sortBy($column->getName(), SORT_STRING | SORT_FLAG_CASE) : $this->collection->sortByDesc($column->getName(), SORT_STRING | SORT_FLAG_CASE);
                }

                return;
            }
        }

        throw new \Exception('Invalid sort column');
    }

    public function countRawResults()
    {
        $this->fullDataLength = $this->collection->count();
    }

    protected function setupLimit()
    {
        $this->collection = $this->collection->slice($this->offset, $this->limit);
    }
}
