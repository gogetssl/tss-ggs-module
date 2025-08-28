<?php

use ModulesGarden\TTSGGSModule\Core\Hook\HookManager;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ProductDuplicate;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ConfigOptionsLoaded;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleActivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleUpgraded;
use function ModulesGarden\TTSGGSModule\Core\listen;

return [
    'bootstrap' => function() {
        HookManager::create(__DIR__, true);
        ProductDuplicate::checkAndInitDuplicateProcess();

        listen(ModuleActivated::class,\ModulesGarden\TTSGGSModule\Packages\Product\Listeners\ModuleActivated::class);
        listen(ModuleUpgraded::class,\ModulesGarden\TTSGGSModule\Packages\Product\Listeners\ModuleUpgraded::class);
        listen(ConfigOptionsLoaded::class,\ModulesGarden\TTSGGSModule\Packages\Product\Listeners\ConfigOptionsLoaded::class);

        //Register Configuration Service
        \ModulesGarden\TTSGGSModule\Core\DependencyInjection\Container::getInstance()
            ->singleton(\ModulesGarden\TTSGGSModule\Packages\Product\Services\Configuration::class);
    },
    'packages'    => [
        'ModuleSettings',
    ],
];
