<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Currency;

use ModulesGarden\TTSGGSModule\Core\Helper\Currency\Models\Format;

/**
 * @deprecated - use \ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Price instead
 */
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

    public function format(float $price, ?string $prefix = null, ?string $suffix  = null, ?Format $format  = null):string
    {
        $format = $format ?: $this->format;
        $newFormat = new \ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Models\Format(
            $format->getDecimals(),$format->getDecimalSeparator(), $format->getThousandsSeparator()
        );

        return \ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Price::format($price, $prefix ?: $this->prefix, $suffix ?: $this->suffix, $newFormat);
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
}