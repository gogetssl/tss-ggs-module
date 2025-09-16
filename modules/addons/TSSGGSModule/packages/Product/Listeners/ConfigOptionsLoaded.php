<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Listeners;

use ModulesGarden\TSSGGSModule\Core\Events\Listener;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\LogActivity;
use ModulesGarden\TSSGGSModule\Packages\Product\Database\Tables\TablesManager;
use Illuminate\Database\Capsule\Manager as DB;

class ConfigOptionsLoaded extends Listener
{
    public function handle($payload = [])
    {
        try
        {
            TablesManager::processSchemaQueries();
            TablesManager::processUpgradeQueries();
        }
        catch (\Throwable $ex)
        {
            LogActivity::error($ex->getMessage());
        }
    }
}
