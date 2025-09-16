<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Listeners;

use ModulesGarden\TSSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TSSGGSModule\Core\Events\Listener;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\LogActivity;

class ModuleUpgraded extends Listener
{
    public function handle($payload = [])
    {
        try
        {
            (new FileLoader())->performQueryFromFile(ModuleConstants::getFullPath('packages', 'Product', 'resources', 'database', 'schema.sql'));
        }
        catch (\Throwable $ex)
        {
            LogActivity::error($ex->getMessage());
        }
    }
}