<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Source;

use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TTSGGSModule\Components\Graph\Series\ExtendedSeries;

abstract class GraphExtendedSeries extends BaseGraph
{
    /**
     * @deprecated - use addSeries instead
     */
    public function addDataSet(DataSet $dataSet)
    {
        $series = new ExtendedSeries($dataSet->getLabel(), $dataSet->getData());

        if ($color = $dataSet->toArray()['borderColor'])
        {
            $series->setColor($color);
        }

        $this->addSeries($series);

        return $this;
    }

    public function addSeries(ExtendedSeries $series):self
    {
        $this->options->addSeries($series);

        return $this;
    }
}