<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\Date;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

abstract class BaseDate
{
    const DEFAULT_TIME_FORMAT = "H:i:s";

    protected string $format;
    protected static string $moduleFormatSetting  = "";

    public abstract function getDefaultFormat():string;

    public function __construct(?string $format = null)
    {
        if (!empty($format))
        {
            $this->format = $format;
            return;
        }

        if ($defaultModuleDateFormat = self::getModuleFormatFromConfig())
        {
            $this->format = $defaultModuleDateFormat;
            return;
        }

        $this->format = $this->getDefaultFormat();
    }

    public static function makeFromModuleConfigFormat():self
    {
        $defaultModuleDateFormat = self::getModuleFormatFromConfig();

        if (empty($defaultModuleDateFormat))
        {
            throw new \Exception("Default date format is not defined. Please define it in module config");
        }

        return new static($defaultModuleDateFormat);
    }

    public function format($date):?string
    {
        if (empty($date))
        {
            return null;
        }

        if (is_string($date))
        {
            $date = new \DateTime($date);
        }

        if (!($date instanceof \DateTimeInterface))
        {
            throw new \Exception("Date type not supported");
        }

        return $date->format($this->format);
    }

    public function setFormat(string $format):self
    {
        $this->format = $format;

        return $this;
    }

    protected static function getModuleFormatFromConfig():?string
    {
        return Config::get(static::$moduleFormatSetting);
    }
}