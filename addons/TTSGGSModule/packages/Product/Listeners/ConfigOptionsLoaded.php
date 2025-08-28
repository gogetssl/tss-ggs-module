<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Listeners;

use ModulesGarden\TTSGGSModule\Core\Events\Listener;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;
use ModulesGarden\TTSGGSModule\Packages\Product\Database\Tables\TablesManager;
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
