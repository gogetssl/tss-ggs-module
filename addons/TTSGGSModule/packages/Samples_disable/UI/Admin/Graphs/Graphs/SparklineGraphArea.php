<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs;

use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class SparklineGraphArea extends \ModulesGarden\TTSGGSModule\Components\Graph\SparklineGraphArea implements AdminAreaInterface
{
    public function loadHtml():void
    {
        $this->setTitle("Dobry tytuł");
        $this->setSubTitle("Nie gorszy pod tytuł");
    }

    public function loadData(): void
    {
        $dataSet = new DataSet();
        $dataSet->setTitle('Data Set')
            ->setData([rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10)]);

        $this->addDataSet($dataSet);
    }
}