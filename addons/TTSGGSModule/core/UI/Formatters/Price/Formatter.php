<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Models\Format;

class Formatter
{
    protected string $prefix;
    protected string $suffix;
    protected Format $format;

    public function __construct(string $prefix = "", string $suffix = "", ?Format $format = null)
    {
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->format = $format ?: new Format();
    }

    public function format(float $price):string
    {
        return $this->prefix . $this->getFormatted($price) . $this->suffix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @param Format $format
     */
    public function setFormat(Format $format): self
    {
        $this->format = $format;

        return $this;
    }

    protected function getFormatted(float $price):string
    {
        return number_format(
            $price,
            $this->format->getDecimals(),
            $this->format->getDecimalSeparator(),
            $this->format->getThousandsSeparator()
        );
    }
}