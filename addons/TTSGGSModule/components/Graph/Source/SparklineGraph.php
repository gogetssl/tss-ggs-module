<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Source;

abstract class SparklineGraph extends GraphExtendedSeries
{
    public function __construct()
    {
        parent::__construct();

        $this->options->chart->addAdditionalParameter('height', 160);
        $this->options->chart->addAdditionalParameter('sparkline', ['enabled' => true]);
        $this->options->tooltip->addAdditionalParameter('fixed', ['enabled' => false]);
        $this->options->tooltip->addAdditionalParameter('x', ['show' => false]);
        $this->options->tooltip->addAdditionalParameter('y', ['title' => ["formatter" =>  "function (seriesName) {
                return ''
              }"]]);
        $this->options->tooltip->addAdditionalParameter('marker', ['show' => false]);

        $this->options->title->addAdditionalParameter('style', ['fontSize' => "24px"]);
        $this->options->title->addAdditionalParameter('offsetX', 0);
        $this->options->subTitle->addAdditionalParameter('style', ['fontSize' => "14px"]);
        $this->options->subTitle->addAdditionalParameter('offsetX', 0);

        $this->options->yAxis->show = false;
        $this->options->legend->show = false;
    }

    public function setTitle(string $title):self
    {
        $this->options->title->text = $title;

        return $this;
    }

    public function setSubTitle(string $title):self
    {
        $this->options->subTitle->text = $title;

        return $this;
    }
}