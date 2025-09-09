<?php

namespace ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Listeners;

use ModulesGarden\TSSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TSSGGSModule\Core\Events\Listener;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class ModuleActivated extends Listener
{
    public function handle($payload = [])
    {
        (new FileLoader())
            ->performQueryFromFile(ModuleConstants::getFullPath('packages', 'ModuleSettings', 'Database', 'Schema.sql'));
    }
}
