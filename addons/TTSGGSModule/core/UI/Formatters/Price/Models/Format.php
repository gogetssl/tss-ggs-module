<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Price\Models;

class Format
{
    protected int $decimals;
    protected string $decimalSeparator;
    protected string $thousandsSeparator;

    public function __construct(int $decimals = 0, string $decimalSeparator = ".", string $thousandsSeparator = "")
    {
        $this->decimals = $decimals;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
    }

    /**
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->decimals;
    }

    /**
     * @return string
     */
    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    /**
     * @return string
     */
    public function getThousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }
}