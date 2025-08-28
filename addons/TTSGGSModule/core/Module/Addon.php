<?php

namespace ModulesGarden\TTSGGSModule\Core\Module;

use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Activate\After;
use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Activate\Before;
use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\PatchManager;
use ModulesGarden\TTSGGSModule\Core\Database\FileLoader;
use ModulesGarden\TTSGGSModule\Core\Events\Events\AfterModuleActivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\AfterModuleUpgraded;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleActivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleDeactivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleUpgraded;
use ModulesGarden\TTSGGSModule\Core\Events\Events\PreModuleActivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\PreModuleUpgraded;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use function ModulesGarden\TTSGGSModule\Core\fire;

class Addon
{
    static function activate(array $params = []): void
    {
        fire(PreModuleActivated::class);

        //Before module activation
        ServiceLocator::call(Before::class)->execute($params);

        //Module Activation
        $fileLoader = new FileLoader();
        $fileLoader->performQueryFromFile(ModuleConstants::getFullPath('app', 'Database', 'schema.sql'));
        $fileLoader->performQueryFromFile(ModuleConstants::getFullPath('app', 'Database', 'data.sql'));

        //After module activation
        ServiceLocator::call(After::class)->execute($params);

        fire(ModuleActivated::class);
        fire(AfterModuleActivated::class);
    }

    static function deactivate(array $params = []): void
    {
        // Before module deactivation
        ServiceLocator::call(\ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Deactivate\Before::class)->execute($params);

        // After module deactivation
        ServiceLocator::call(\ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Deactivate\After::class)->execute($params);

        fire(ModuleDeactivated::class);
    }

    static function upgrade(array $params, bool $force = false): void
    {
        fire(PreModuleUpgraded::class);

        // Before module upgrade
        ServiceLocator::call(\ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\Before::class)->execute($params);

        //Module Upgrade
        if (empty($params['version']))
        {
            throw new \Exception('No version specified');
        }

        (new PatchManager())->run($params['version'], $force);

        // After module upgrade
        ServiceLocator::call(\ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\After::class)->execute($params);

        fire(ModuleUpgraded::class);
        fire(AfterModuleUpgraded::class);
    }
}