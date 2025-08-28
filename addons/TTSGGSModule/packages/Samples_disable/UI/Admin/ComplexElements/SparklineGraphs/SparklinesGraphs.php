<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SparklineGraphs;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\SparklineGraphArea;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\SparklineGraphLine;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;

class SparklinesGraphs extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle("Test Grafów");

        $grid = new Grid();

        $chart1       = new SparklineGraphLine();
        $chart2       = new SparklineGraphLine();
        $chart3       = new SparklineGraphArea();
        $chart4       = new SparklineGraphArea();
        $leftSection  = new Container();
        $rightSection = new Grid();

        $rightSection->setRows([
            [
                [$chart1, 6], [$chart2, 6]
            ],
            [
                [$chart3, 6], [$chart4, 6]
            ]
        ]);

        $grid->setRows([
            [
                [$leftSection, 6], [$rightSection, 6]
            ]]);

        $widget->addElement($grid);
        $this->addElement($widget);
    }
}