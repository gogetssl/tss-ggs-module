<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;

/**
 * "Clean log": removes every entry of the module log
 */
class CleanLogProvider extends CrudProvider
{
    public function delete()
    {
        if (!Config::get('logs.delete_logs.enabled', true))
        {
            throw new \Exception('deletingLogsIsNotAllowed');
        }

        Logs::query()->delete();
    }
}
