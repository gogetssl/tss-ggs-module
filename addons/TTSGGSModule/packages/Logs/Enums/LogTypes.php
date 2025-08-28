<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\Enums;

class LogTypes
{
    public const ALERT     = 'alert';
    public const CRITICAL  = 'critical';
    public const DEBUG     = 'debug';
    public const EMERGENCY = 'emergency';
    public const ERROR     = 'error';
    public const INFO      = 'info';
    public const NOTICE    = 'notice';
    public const WARNING   = 'warning';

    public const TYPES = [
        self::ALERT,
        self::CRITICAL,
        self::DEBUG,
        self::EMERGENCY,
        self::ERROR,
        self::INFO,
        self::NOTICE,
        self::WARNING
    ];

    public static function getAvailable(): array
    {
        return self::TYPES;
    }
}
