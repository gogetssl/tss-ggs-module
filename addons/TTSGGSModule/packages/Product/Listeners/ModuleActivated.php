<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Listeners;

use ModulesGarden\TTSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TTSGGSModule\Core\Events\Listener;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;

class ModuleActivated extends Listener
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