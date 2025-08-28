<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters\DataModels;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelWithHeadersInterface;
use ModulesGarden\TTSGGSModule\Core\Exporters\Source\BaseDataModel;
use \Illuminate\Contracts\Support\Arrayable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;

class ArrayData extends BaseDataModel implements DataModelWithHeadersInterface, IteratorAggregate, Arrayable
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getHeaders():array
    {
        return parent::combineHeaders($this->data[0]);
    }

    public function getItemValuesByKey(int $key)
    {
        return $this->data[$key];
    }

    public function getContentData()
    {
        $data = $this->data;
        unset($data[0]);
        return $data;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}