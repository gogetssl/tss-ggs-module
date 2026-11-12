<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Error line of a cron job that works through many orders: how many failed and which ones, so one broken order is
 * recognised as such on the dashboard instead of looking like a broken job
 */
class CronErrors
{
    /**
     * @param array $failed serviceId => message
     */
    public static function summary(array $failed, $total, $show = 3)
    {
        $parts = [];
        foreach(array_slice($failed, 0, $show, true) as $serviceId => $message)
        {
            $parts[] = 'service #' . $serviceId . ': ' . trim(preg_replace('/\s+/', ' ', $message));
        }

        return count($failed) . ' of ' . (int)$total . ' orders failed - ' . implode('; ', $parts) . (count($failed) > $show ? '; and ' . (count($failed) - $show) . ' more' : '') . ' (details in the module log)';
    }
}
