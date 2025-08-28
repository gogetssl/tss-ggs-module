<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use Exception;
use ModulesGarden\TTSGGSModule\Core\Contracts\ArrayProviderInterface;

class ObjectDataProvider extends AbstractRecordsListDataProvider implements ArrayProviderInterface
{
    protected $data;
    protected $requiredObjectClass;

    public function __construct($requiredObjectClass)
    {
        $this->requiredObjectClass = $requiredObjectClass;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data): ArrayProviderInterface
    {
        foreach ($data as $element)
        {
            if (!$this->checkElement($element))
            {
                throw new Exception('Incorrect object given');
            }
        }

        $this->data = $data;

        return $this;
    }

    public function checkElement($element)
    {
        return $element instanceof $this->requiredObjectClass;
    }
}
