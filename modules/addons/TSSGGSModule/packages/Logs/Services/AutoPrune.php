<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\Services;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use WHMCS\Database\Capsule as DB;

/**
 * Automatially remoces records older than value specified in configuration
 */
class AutoPrune
{
    public function run()
    {
        if (Config::get('logs.auto_prune.enabled', true) && ModuleSettings::get('logs.auto_prune'))
        {
            \ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs::where('date', '<', DB::raw('DATE_SUB(NOW(), INTERVAL ' . ((int)ModuleSettings::get('logs.auto_prune_older_than')) . ' day)'))->delete();
        }
    }
}