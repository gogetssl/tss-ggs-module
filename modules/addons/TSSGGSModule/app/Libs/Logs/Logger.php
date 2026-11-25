<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Logs;

use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\LogPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Support\ModuleSettings;
use ModulesGarden\TSSGGSModule\App\Models\Log;

/**
 * Writes the module log (admin page "Logs"):
 *
 *   Logger::info('Pricing for product #:id has been updated', ['id' => 12]);
 *   Logger::error($e->getMessage(), ['service' => $serviceId]);
 *
 * $data is stored with the entry; "service" (a WHMCS service ID) makes the entry findable by service and "api" marks
 * a gateway request. What is recorded follows Settings > Logs (GeneralSettings, LogPolicy): the log can be off,
 * events can be off, only some types may be kept. The same message for the same service within a few hours (a
 * failing cron retried every 5 minutes) is one row with a counter.
 */
class Logger
{
    const REPEAT_WINDOW = 21600; // 6 hours

    public static function log(string $level, $message, array $data = []): void
    {
        $message = (string)$message;
        // Settings > Logs > Module Log
        if(!GeneralSettings::enabled('internalLog'))
        {
            return;
        }

        // events (not failures, not gateway requests) can be switched off; services in debug mode are always recorded
        if(!LogPolicy::isFailure($level) && !isset($data['api']) && !LogPolicy::logEvent($data['service'] ?? null))
        {
            return;
        }

        // only the chosen types (Logs > Actions > Settings); none chosen = every type
        $types = ModuleSettings::get('logs.types', []);
        if(!empty($types) && !in_array($level, (array)$types))
        {
            return;
        }

        // not for a service in debug mode: there every call keeps its own time
        $last = null;
        foreach(LogPolicy::debugging($data['service'] ?? null) ? [] : Log::where('type', $level)->where('message', $message)->where('date', '>=', date('Y-m-d H:i:s', time() - self::REPEAT_WINDOW))->orderBy('id', 'desc')->limit(50)->get() as $candidate)
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
            $last->update(['date' => date('Y-m-d H:i:s'), 'data' => $data]);

            return;
        }

        Log::create(['type' => $level, 'date' => date('Y-m-d H:i:s'), 'message' => $message, 'data' => $data]);
    }

    public static function emergency($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function alert($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function critical($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function error($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function warning($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function notice($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function info($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    public static function debug($message, array $data = []): void
    {
        self::log(__FUNCTION__, $message, $data);
    }

    /** Entries older than the days set under Logs > Actions > Settings are removed (once a day, App\Kernel\Hooks) */
    public static function prune(): void
    {
        if(ModuleSettings::get('logs.auto_prune', 1))
        {
            $days = ModuleSettings::get('logs.auto_prune_older_than', 30);
            Log::where('date', '<', date('Y-m-d H:i:s', time() - max(1, is_numeric($days) ? (int)$days : 30) * 86400))->delete();
        }
    }
}
