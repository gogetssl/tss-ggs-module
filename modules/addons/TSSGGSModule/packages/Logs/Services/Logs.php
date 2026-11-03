<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\Services;

use DateTime;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\LogPolicy;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs as Log;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use Psr\Log\LoggerInterface;

class Logs implements LoggerInterface
{
    const REPEAT_WINDOW = 21600; // 6 hours

    public function alert($message, array $data = [])
    {
        $this->log(__FUNCTION__, $message, $data);
    }

    public function log($level, $message, array $data = [])
    {
        // Settings > Logs > Module Log
        if(!GeneralSettings::enabled('internalLog'))
        {
            return;
        }

        // Events (not failures, not gateway requests) can be switched off; services in debug mode are always recorded
        if(!LogPolicy::isFailure((string)$level) && !isset($data['api']) && !LogPolicy::logEvent($data['service'] ?? null))
        {
            return;
        }

        $settings = ModuleSettings::get('logs.types', []);
        //log only selected types
        if (!empty($settings) && !in_array($level, $settings))
        {
            return;
        }

        // The same message for the same service within a few hours (a failing cron retried every 5 minutes) is one row with a counter
        $last = null;
        foreach(Log::where('type', $level)->where('message', $message)->where('date', '>=', date('Y-m-d H:i:s', time() - self::REPEAT_WINDOW))->orderBy('id', 'desc')->limit(50)->get() as $candidate)
        {
            if((((array)$candidate->data)['service'] ?? null) == ($data['service'] ?? null))
            {
                $last = $candidate;
                break;
            }
        }
        if($last)
        {
            $old             = (array)$last->data;
            $data['repeats'] = (int)($old['repeats'] ?? 0) + 1;
            $data['since']   = $old['since'] ?? (string)$last->date;
            $last->update(['date' => (new DateTime())->format('Y-m-d H:i:s'), 'data' => $data]);

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
