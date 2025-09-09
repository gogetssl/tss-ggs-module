<?php

use ModulesGarden\TSSGGSModule\Core\Hook\HookManager;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ProductDuplicate;
use ModulesGarden\TSSGGSModule\Core\Events\Events\ConfigOptionsLoaded;
use ModulesGarden\TSSGGSModule\Core\Events\Events\ModuleActivated;
use ModulesGarden\TSSGGSModule\Core\Events\Events\ModuleUpgraded;
use function ModulesGarden\TSSGGSModule\Core\listen;

return [
    'bootstrap' => function() {
        HookManager::create(__DIR__, true);
        ProductDuplicate::checkAndInitDuplicateProcess();

        listen(ModuleActivated::class,\ModulesGarden\TSSGGSModule\Packages\Product\Listeners\ModuleActivated::class);
        listen(ModuleUpgraded::class,\ModulesGarden\TSSGGSModule\Packages\Product\Listeners\ModuleUpgraded::class);
        listen(ConfigOptionsLoaded::class,\ModulesGarden\TSSGGSModule\Packages\Product\Listeners\ConfigOptionsLoaded::class);

        //Register Configuration Service
        \ModulesGarden\TSSGGSModule\Core\DependencyInjection\Container::getInstance()
            ->singleton(\ModulesGarden\TSSGGSModule\Packages\Product\Services\Configuration::class);
    },
    'packages'    => [
        'ModuleSettings',
    ],
];
