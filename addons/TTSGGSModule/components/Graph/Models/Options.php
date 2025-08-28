<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models;

use ModulesGarden\TTSGGSModule\Components\Graph\Options\ChartOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\ColorsOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\DataLabelsOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\FillOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\LabelsOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\LegendOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\PlotOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\ResponsiveOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\StatesOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\StrokeOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\SubTitleOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\TitleOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\TooltipOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\XaxisOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Options\YaxisOptions;
use ModulesGarden\TTSGGSModule\Components\Graph\Source\SeriesInterface;

class Options
{
    protected const REQUIRED_OPTIONS = ['chart', 'series'];

    public array $series;
    public array $responsive;

    public ChartOptions $chart;
    public ColorsOptions $colors;
    public DataLabelsOptions $dataLabels;
    public FillOptions $fill;
    public LabelsOptions $labels;
    public LegendOptions $legend;
    public PlotOptions $plot;
    public StatesOptions $states;
    public StrokeOptions $stroke;
    public TitleOptions $title;
    public SubTitleOptions $subTitle;
    public TooltipOptions $tooltip;
    public XaxisOptions $xAxis;
    public YaxisOptions $yAxis;

    public function __construct()
    {
        $this->series      = [];
        $this->responsive  = [];
        $this->chart       = new ChartOptions();
        $this->colors      = new ColorsOptions();
        $this->title       = new TitleOptions();
        $this->subTitle    = new SubTitleOptions();
        $this->xAxis       = new XaxisOptions();
        $this->yAxis       = new YaxisOptions();
        $this->dataLabels  = new DataLabelsOptions();
        $this->fill        = new FillOptions();
        $this->labels      = new LabelsOptions();
        $this->legend      = new LegendOptions();
        $this->plot        = new PlotOptions();
        $this->states      = new StatesOptions();
        $this->stroke      = new StrokeOptions();
        $this->tooltip     = new TooltipOptions();
    }

    public function addSeries( $series):self
    {
        $this->series[] = $series;

        return $this;
    }

    public function setSeries(array $series):self
    {
        foreach ($series as $data)
        {
            $this->addSeries($data);
        }

        return $this;
    }

    public function addResponsiveOption(ResponsiveOptions $responsiveOption):self
    {
        $this->responsive[] = $responsiveOption;

        return $this;
    }

    public function setChartOptions(ChartOptions $chartOptions):self
    {
        $this->chart = $chartOptions;

        return $this;
    }

    public function setTitleOptions(TitleOptions $titleOption):self
    {
        $this->title = $titleOption;

        return $this;
    }

    public function setXaxisOptions(XaxisOptions $xAxisOptions):self
    {
        $this->xAxis = $xAxisOptions;

        return $this;
    }

    public function setYaxisOptions(YaxisOptions $yAxisOptions):self
    {
        $this->yAxis = $yAxisOptions;

        return $this;
    }

    public function setDataLabelsOptions(DataLabelsOptions $dataLabelsOptions):self
    {
        $this->dataLabels = $dataLabelsOptions;

        return $this;
    }

    public function setLabelsOptions(LabelsOptions $labelsOptions):self
    {
        $this->labels = $labelsOptions;

        return $this;
    }

    public function setLegendOptions(LegendOptions $legendOptions):self
    {
        $this->legend = $legendOptions;

        return $this;
    }


    public function setPlotOptions(PlotOptions $plotOptions):self
    {
        $this->plot = $plotOptions;

        return $this;
    }

    public function setTooltipOptions(TooltipOptions $tooltipOptions):self
    {
        $this->tooltip = $tooltipOptions;

        return $this;
    }

    public function toArray()
    {
        return array_filter([
            'chart'       => $this->chart->toArray(),
            'colors'      => $this->colors->toArray(),
            'series'      => array_map(function (SeriesInterface $series) { return $series->getSeries(); }, $this->series),
            'title'       => $this->title->toArray(),
            'subtitle'    => $this->subTitle->toArray(),
            'xaxis'       => $this->xAxis->toArray(),
            'yaxis'       => $this->yAxis->toArray(),
            'dataLabels'  => $this->dataLabels->toArray(),
            'fill'        => $this->fill->toArray(),
            'labels'      => $this->labels->toArray(),
            'legend'      => $this->legend->toArray(),
            'plotOptions' => $this->plot->toArray(),
            'tooltip'     => $this->tooltip->toArray(),
            'states'      => $this->states->toArray(),
            'stroke'      => $this->stroke->toArray(),
            'responsive'  => array_map(function ($element) { return $element->toArray(); }, $this->responsive),
        ], function ($value, $key) {return in_array($key, self::REQUIRED_OPTIONS) || !empty($value); }, ARRAY_FILTER_USE_BOTH );
    }

}