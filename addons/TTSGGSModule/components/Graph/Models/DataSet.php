<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models;

/**
 * @deprecated - use Series instead
 */
class DataSet
{
    public const STEPPED_LINE_AFTER       = 'after';
    public const STEPPED_LINE_BEFORE      = 'before';
    public const STEPPED_LINE_FALSE       = false;
    public const STEPPED_LINE_TRUE        = false;

    /**
     * @var string[]|string
     */
    protected $backgroundColor = [];

    /**
     * @var string
     */
    protected $borderCapStyle = null;

    /**
     * @var string[]|string
     */
    protected $borderColor = [];

    /**
     * @var float[]|int[]
     */
    protected $borderDash = [];

    /**
     * @var float|int
     */
    protected $borderDashOffset = null;

    /**
     * @var string
     */
    protected $borderJoinStyle = null;

    /**
     * @var string
     */
    protected $borderSkipped = null;

    /**
     * @var float[]|float|int[]|int
     */
    protected $borderWidth = [];

    /**
     * @var string
     */
    protected $cubicInterpolationMode = null;

    /**
     * @var float[]|int[]|array
     */
    protected $data = [];

    /**
     * @var bool|string
     */
    protected $fill = null;

    /**
     * @var string[]|string[]
     */
    protected $hoverBackgroundColor = [];

    /**
     * @var string[]|string[]
     */
    protected $hoverBorderColor = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $hoverBorderWidth = [];
    protected $label = null;

    /**
     * @var float|int
     */
    protected $lineTension = null;

    /**
     * @var string[]|string
     */
    protected $pointBackgroundColor = [];

    /**
     * @var string[]|string
     */
    protected $pointBorderColor = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $pointBorderWidth = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $pointHitRadius = [];

    /**
     * @var string[]|string
     */
    protected $pointHoverBackgroundColor = [];

    /**
     * @var string[]|string
     */
    protected $pointHoverBorderColor = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $pointHoverBorderWidth = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $pointHoverRadius = [];

    /**
     * @var float[]|float|int[]|int
     */
    protected $pointRadius = [];

    /**
     * @var string[]|string
     */
    protected $pointStyle = [];

    /**
     * @var bool
     */
    protected $showLine = null;

    /**
     * @var bool
     */
    protected $spanGaps = null;

    /**
     * @var bool|string
     */
    protected $steppedLine = false;
    protected $type = null;

    /**
     * @var string
     */
    protected $xAxisID = null;

    /**
     * @var string
     */
    protected $yAxisID = null;

    /**
     * @var bool
     */
    protected $hidden = false;

    public function setBorderCapStyle($borderCapStyle = ''):self
    {
        $this->borderCapStyle = $borderCapStyle;

        return $this;
    }

    /**
     * @param float $borderDashOffset
     * @return $this
     */
    public function setBorderDashOffset($borderDashOffset = 0):self
    {
        $this->borderDashOffset = $borderDashOffset;

        return $this;
    }

    public function setBorderJoinStyle($borderJoinStyle = ''):self
    {
        $this->borderJoinStyle = $borderJoinStyle;

        return $this;
    }

    public function setBorderSkipped($borderSkipped = true):self
    {
        $this->borderSkipped = $borderSkipped;

        return $this;
    }

    /**
     * @param float $borderWidth
     * @return $this
     */
    public function setBorderWidth($borderWidth = 0):self
    {
        $this->borderWidth = $borderWidth;

        return $this;
    }

    public function setConfigurationDataSet(array $configuration = []):self
    {
        foreach ($configuration as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    public function setCubicInterpolationMode($cubicInterpolationMode = ''):self
    {
        $this->cubicInterpolationMode = $cubicInterpolationMode;

        return $this;
    }

    public function setData($data = []):self
    {
        foreach ($data as $value)
        {
            $this->addDataItem($value, []);
        }

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addDataItem($value, array $configuration = []):self
    {
        $this->data[] = $value;

        foreach ($configuration as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key}[] = $value;
            }
        }

        return $this;
    }

    public function setFill($fill = ''):self
    {
        $this->fill = $fill;

        return $this;
    }

    public function setLineTension($lineTension = 0):self
    {
        $this->lineTension = $lineTension;

        return $this;
    }

    public function setShowLine($showLine = true):self
    {
        $this->showLine = $showLine;

        return $this;
    }

    public function setSpanGaps($spanGaps = true):self
    {
        $this->spanGaps = $spanGaps;

        return $this;
    }

    public function setSteppedLine($steppedLine = self::STEPPED_LINE_FALSE):self
    {
        if (in_array($steppedLine, [self::STEPPED_LINE_FALSE, self::STEPPED_LINE_TRUE, self::STEPPED_LINE_BEFORE, self::STEPPED_LINE_AFTER], true))
        {
            $this->steppedLine = $steppedLine;
        }

        return $this;
    }

    public function setTitle($title = ''):self
    {
        $this->setLabel($title);

        return $this;
    }

    public function getLabel():string
    {
        return $this->label;
    }

    /**
     * Title label in Legend
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label = ''):self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type = 'line'):self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Title label in Legend
     *
     *
     * @param bool $hidden
     * @return $this
     */
    public function setHidden(bool $hidden = true):self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function setXAxisID($xAxisID = ''):self
    {
        $this->xAxisID = $xAxisID;

        return $this;
    }

    public function setYAxisID($yAxisID = ''):self
    {
        $this->yAxisID = $yAxisID;

        return $this;
    }

    public function toArray():array
    {
        $return = [];

        foreach ($this as $key => $value)
        {
            if (property_exists($this, $key) && ($value !== null && !is_array($value) || is_array($value) && count($value) > 0))
            {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
