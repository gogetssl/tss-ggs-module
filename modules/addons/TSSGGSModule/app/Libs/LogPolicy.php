<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * What the Module Log records. Three settings decide it (Settings > Logs, and Actions > Settings on the Logs page):
 *
 *   API requests   off | errors | changes | all      requests to the certificate gateway and their answers
 *   Events         on / off                          what the crons and order actions did (status changes, notices, invoices)
 *   Debug services service IDs + hours               everything for those services, for a limited time
 *
 * Failures (errors, exceptions) are always recorded while the Module Log is on. A service in debug mode is recorded in
 * full - every request including reads, every event - whatever the other settings say, until the time runs out.
 */
class LogPolicy
{
    const API_OFF     = 'off';
    const API_ERRORS  = 'errors';
    const API_CHANGES = 'changes';
    const API_ALL     = 'all';

    const API_MODES   = [self::API_OFF, self::API_ERRORS, self::API_CHANGES, self::API_ALL];
    const DEBUG_HOURS = [1, 4, 12, 24, 72];

    /** levels that are failures: always recorded */
    const FAILURE_LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning'];

    public static function apiMode(): string
    {
        $mode = (string)GeneralSettings::get('apiLogMode');

        return in_array($mode, self::API_MODES, true) ? $mode : self::API_CHANGES;
    }

    /** Service IDs in debug mode right now (empty once the time is up) */
    public static function debugServices(): array
    {
        if((int)GeneralSettings::get('debugUntil') <= time())
        {
            return [];
        }

        return self::parseIds((string)GeneralSettings::get('debugServices'));
    }

    public static function debugUntil(): int
    {
        return self::debugServices() ? (int)GeneralSettings::get('debugUntil') : 0;
    }

    public static function debugging($serviceId): bool
    {
        return $serviceId && in_array((int)$serviceId, self::debugServices(), true);
    }

    /**
     * Should a gateway request be recorded?
     * Failed requests: unless API logging is off. Requests that change something (order, validation, reissue, cancel):
     * "changes" and "all". Successful reads: "all" only - the crons read every pending order every few minutes.
     */
    public static function logApi(bool $failed, string $method, $serviceId): bool
    {
        if(self::debugging($serviceId))
        {
            return true;
        }

        switch(self::apiMode())
        {
            case self::API_ALL:
                return true;
            case self::API_CHANGES:
                return $failed || $method !== 'GET';
            case self::API_ERRORS:
                return $failed;
            default:
                return false;
        }
    }

    /** Should an event (not a failure, not a gateway request) be recorded? */
    public static function logEvent($serviceId): bool
    {
        return GeneralSettings::enabled('eventLog') || self::debugging($serviceId);
    }

    public static function isFailure(string $level): bool
    {
        return in_array($level, self::FAILURE_LEVELS, true);
    }

    /** "71, #58 ; 12" -> [71, 58, 12] */
    public static function parseIds(string $text): array
    {
        preg_match_all('/\d+/', $text, $matches);

        return array_values(array_unique(array_filter(array_map('intval', $matches[0]))));
    }

    /**
     * The settings to store from posted debug values: the list cleaned up and the end time
     * (empty list or no hours = debug mode off)
     */
    public static function debugSettings($services, $hours): array
    {
        $ids   = self::parseIds((string)$services);
        $hours = (int)$hours;

        return [
            'debugServices' => implode(', ', $ids),
            'debugUntil'    => $ids && $hours > 0 ? time() + $hours * 3600 : 0,
        ];
    }
}
