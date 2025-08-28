<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use JsonSerializable;
use ModulesGarden\TTSGGSModule\Core\Contracts\DataSetInterface;

class DataSet implements DataSetInterface, JsonSerializable
{
    public $fullDataLength = 0;
    public $offset = 0;
    public $records = [];
    protected $fieldModifiers = [];

    protected string $sortBy = '';
    protected string $sortDir = '';

    /**
     * @param \ArrayAccess $arrayAccess
     * @param int $offset
     * @param int $fullDataLength
     * @param string $sortBy
     * @param string $sortDir
     * @throws \Exception
     */
    public function __construct(\ArrayAccess $arrayAccess, int $offset, int $fullDataLength, string $sortBy = '', string $sortDir = '')
    {
//        if (!is_array($arrayOrArrayAccess) && !is_a($arrayOrArrayAccess, \ArrayAccess::class))
//        {
//            throw new \Exception('Invalid only. Only Array or \ArrayAccess is accepted');
//        }

        $this->records        = $arrayAccess;
        $this->fullDataLength = $fullDataLength;
        $this->offset         = $offset;
        $this->sortBy         = $sortBy;
        $this->sortDir        = $sortDir;
    }

    public function getFullLength(): int
    {
        return $this->fullDataLength;
    }

    public function getLength(): int
    {
        return count($this->records);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getRecords()
    {
        return $this->records;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'offset'         => $this->offset,
            'fullDataLength' => $this->fullDataLength,
            'records'        => is_array($this->records) ? array_values($this->records) : array_values($this->records->toArray()),
            'sort'           => [
                'by'  => $this->sortBy,
                'dir' => $this->sortDir
            ]
        ];
    }

    public function modifyRecords(): self
    {
        if (!$this->fieldModifiers)
        {
            return $this;
        }

        foreach ($this->records as $key => &$record)
        {
            $asArray = is_array($record) ? $record : (method_exists($record, 'toArray') ? $record->toArray() : (array)$record);

            foreach ($this->fieldModifiers as $fieldName => $modifier)
            {
                $ret = $modifier($fieldName, $asArray, $asArray[$fieldName], $record);

                /*if (is_array($ret))
                {
                    $record = $ret;
                }
                else*/
                if (is_object($ret) && method_exists($ret, 'toArray'))
                {
                    $asArray[$fieldName] = $ret->toArray();
                }
                elseif (is_object($ret))
                {
                    $asArray[$fieldName] = (array)$ret;
                }
                else
                {
                    $asArray[$fieldName] = $ret;
                }
            }

            $this->records[$key] = $asArray;
        }

        return $this;
    }

    public function setFieldModifier(string $fieldName, callable $callback)
    {
        $this->fieldModifiers[$fieldName] = $callback;
    }
}
