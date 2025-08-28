<?php

namespace ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Listeners;

use ModulesGarden\TTSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TTSGGSModule\Core\Events\Listener;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class ModuleActivated extends Listener
{
    public function handle($payload = [])
    {
        (new FileLoader())
            ->performQueryFromFile(ModuleConstants::getFullPath('packages', 'ModuleSettings', 'Database', 'Schema.sql'));
    }
}
