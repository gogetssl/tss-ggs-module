<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Database\Tables;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\PatchManager;
use ModulesGarden\TTSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TTSGGSModule\Packages\Product\Database\Patches\PatchesManager as DataBasePatches;

class TablesManager
{
    public static function processSchemaQueries(): void
    {
        /*Run create table schema*/
        (new FileLoader())->performQueryFromFile(ModuleConstants::getFullPath('packages', 'Product', 'resources', 'database', 'schema.sql'));

        /*Run patches*/
        (new DataBasePatches())->executeAll();

        /*Run custom schema queries*/
        (new FileLoader())->performQueryFromFile(ModuleConstants::getFullPath('app', 'Database', 'schema.sql'));
        (new FileLoader())->performQueryFromFile(ModuleConstants::getFullPath('app', 'Database', 'data.sql'));
    }

    public static function processUpgradeQueries(): void
    {
        /* Run custom upgrade queries*/
        (new PatchManager(PatchManager::TYPE_SERVER))->run(ModuleSettings::get('server.version', 0));

        ModuleSettings::save(['server.version' => Config::get('configuration.version')]);
    }
}