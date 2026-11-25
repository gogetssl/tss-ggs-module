<?php

use ModulesGarden\TSSGGSModule\Core\DependencyInjection\Container;
use ModulesGarden\TSSGGSModule\Packages\Logs\Listeners\ModuleActivated;
use ModulesGarden\TSSGGSModule\Packages\Logs\Listeners\ModuleUpgraded;
use ModulesGarden\TSSGGSModule\Packages\Logs\Services\Logs;
use function ModulesGarden\TSSGGSModule\Core\listen;

return [
    'menu'        => [
        // the Logs page is in the module's own menu (app/Config/menu/admin.yml)
        'admin'  => [
        ],
        'client' => [

        ],
    ],
    'controllers' => [
        // the Logs page is App\Pages\Admin\Logs (the module's own kernel)
        'admin'  => [
        ],
        'client' => [

        ],
    ],
    'packages'    => [
        'ModuleSettings'
    ],
    'config'      => [
    ],
    'bootstrap'   => function() {
//        $oClass = new \ReflectionClass(\ModulesGarden\TSSGGSModule\Packages\Logs\Enums\LogTypes::class);
//        foreach ($oClass->getConstants() as $type)
//        {
//            call_user_func([\ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger::class, $type], $type. '{adawd}', ['adawd' => $type]);
//        }

        \ModulesGarden\TSSGGSModule\Core\Hook\HookManager::create(__DIR__, true);
        Container::getInstance()->singleton(Logs::class);
        listen(\ModulesGarden\TSSGGSModule\Core\Events\Events\ModuleActivated::class, ModuleActivated::class);
        listen(\ModulesGarden\TSSGGSModule\Core\Events\Events\ModuleUpgraded::class, ModuleUpgraded::class);
    },
];
