<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters\DataModels;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\BaseDataModel;
use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelWithHeadersInterface;
use \Illuminate\Database\Eloquent\Collection as CollectionModel;
use \Illuminate\Contracts\Support\Arrayable;
use Traversable;
use IteratorAggregate;

class Collection extends BaseDataModel implements DataModelWithHeadersInterface, IteratorAggregate, Arrayable
{
    protected CollectionModel $collection;

    public function __construct(CollectionModel $collection)
    {
        $this->collection = $collection;
    }

    public function getHeaders():array
    {
        return parent::combineHeaders(array_keys($this->collection->first()->getAttributes()));
    }

    public function getContentData()
    {
        return $this->collection;
    }

    public function getItemValuesByKey(int $key)
    {
        return $this->collection->get($key)->getAttributes();
    }

    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    public function toArray(): array
    {
        return $this->collection->toArray();
    }
}