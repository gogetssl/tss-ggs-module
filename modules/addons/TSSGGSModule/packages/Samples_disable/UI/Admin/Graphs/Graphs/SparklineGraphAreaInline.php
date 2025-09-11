<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs;

use ModulesGarden\TSSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class SparklineGraphAreaInline extends \ModulesGarden\TSSGGSModule\Components\Graph\InlineGraphArea implements AdminAreaInterface
{
    public function loadHtml():void
    {
    }

    public function loadData(): void
    {
        $dataSet = new DataSet();
        $dataSet->setTitle('Data Set')
            ->setData([rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10)]);

        $this->addDataSet($dataSet);
    }
}