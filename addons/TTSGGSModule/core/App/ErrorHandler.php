<?php

namespace ModulesGarden\TTSGGSModule\Core\App;

class ErrorHandler
{
    public const ERRORS   = [1, 4, 16, 64, 256, 4096];
    public const NOTICES  = [8, 1024, 8192, 16384];
    public const WARNINGS = [2, 32, 128, 512, 2048];

    public function __construct()
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'LowLevelLog.php';
    }

    public function logError($errorToken, $errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        $logType      = $this->getLogType($errno);
        $errorTime    = date('d.m.Y H:i:s', time());
        $errorDetails = [
            'errno'      => $errno,
            'errstr'     => $errstr,
            'errfile'    => $errfile,
            'errline'    => $errline,
            'errcontext' => $logType === 'error' ? $errcontext : null,
        ];

        $log = new LowLevelLog($logType, $errorToken, $errorTime);
        $log->makeLogs($errorDetails);
    }

    public function getLogType($errno = null)
    {
        if (in_array($errno, self::WARNINGS))
        {
            return 'warning';
        }

        if (in_array($errno, self::NOTICES))
        {
            return 'notice';
        }

        return 'error';
    }
}
