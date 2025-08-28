<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models;

use Illuminate\Contracts\Support\Arrayable;

class Data implements Arrayable
{
    /**
     * @var DataSet[]
     */
    protected $datasets = [];
    protected $labels = [];

    public function __construct(array $labels = [], array $datasets = [])
    {
        $this->labels   = $labels;
        $this->datasets = $datasets;
    }

    public function addDataSet(DataSet $dataset)
    {
        $this->datasets[] = $dataset;

        return $this;
    }

    public function addLabel($label = '')
    {
        $this->labels[] = $label;

        return $this;
    }

    public function setDataSets(array $dataSets = [])
    {
        $this->datasets = $dataSets;

        return $this;
    }

    public function setLabels(array $labels = [])
    {
        $this->labels = $labels;

        return $this;
    }

    public function toArray()
    {
        $return = [
            'labels'   => $this->labels,
            'datasets' => [],
        ];

        foreach ($this->datasets as $dataset)
        {
            $return['datasets'][] = $dataset->toArray();
        }

        return $return;
    }
}
