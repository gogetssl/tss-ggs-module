<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Source;

use ModulesGarden\TSSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TSSGGSModule\Components\Graph\Series\ExtendedSeries;

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