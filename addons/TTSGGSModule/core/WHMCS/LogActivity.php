<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use Psr\Log\LoggerInterface;


class LogActivity implements LoggerInterface
{
    public function log($level, $message, array $data = [])
    {
        \logActivity(sprintf("%s [%s] - %s", ModuleConstants::getModuleName(), $level, $message));
    }

    public function alert($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function critical($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function debug($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function emergency($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function error($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function info($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function notice($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function warning($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }
}