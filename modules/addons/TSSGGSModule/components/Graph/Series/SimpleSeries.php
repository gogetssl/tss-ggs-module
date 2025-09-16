<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Series;

use ModulesGarden\TSSGGSModule\Components\Graph\Source\SeriesInterface;

class SimpleSeries implements SeriesInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getSeries()
    {
        return $this->data;
    }
}