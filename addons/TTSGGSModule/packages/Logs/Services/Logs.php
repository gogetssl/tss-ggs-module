<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\Services;

use DateTime;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs as Log;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use Psr\Log\LoggerInterface;

class Logs implements LoggerInterface
{
    public function alert($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function log($level, $message, array $data = [])
    {
        $settings = ModuleSettings::get('logs.types', []);
        //log only selected types
        if (!empty($settings) && !in_array($level, $settings))
        {
            return;
        }

        Log::create([
            'type'    => $level,
            'date'    => (new DateTime())->format('Y-m-d H:i:s'),
            'message' => $message,
            'data'    => $data,
        ]);
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
