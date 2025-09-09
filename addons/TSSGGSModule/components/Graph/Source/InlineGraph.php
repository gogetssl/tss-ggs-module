<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Source;

use ModulesGarden\TSSGGSModule\Components\Graph\Options\SubTitleOptions;
use ModulesGarden\TSSGGSModule\Components\Graph\Options\TitleOptions;
use ModulesGarden\TSSGGSModule\Components\Graph\SparklineGraphLine;

abstract class InlineGraph extends SparklineGraphLine
{
    public function __construct()
    {
        parent::__construct();

        $this->options->title = new TitleOptions();
        $this->options->subTitle = new SubTitleOptions();
        $this->options->tooltip->addAdditionalParameter('y', ['title' => ['formatter' => "function (seriesName) { return '';}"]]);
        $this->options->chart->addAdditionalParameter('height', "35px");
        $this->options->chart->addAdditionalParameter('width', "150px");
        $this->options->stroke->addAdditionalParameter('width', "2");
    }

}