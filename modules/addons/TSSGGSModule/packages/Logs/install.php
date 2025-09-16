<?php

use ModulesGarden\TSSGGSModule\Core\Components\Enums\Type;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection\Container;
use ModulesGarden\TSSGGSModule\Packages\Logs\Enums\LogTypes;
use ModulesGarden\TSSGGSModule\Packages\Logs\Listeners\ModuleActivated;
use ModulesGarden\TSSGGSModule\Packages\Logs\Listeners\ModuleUpgraded;
use ModulesGarden\TSSGGSModule\Packages\Logs\Services\Logs;
use function ModulesGarden\TSSGGSModule\Core\listen;

return [
    'menu'        => [
        'admin'  => [
            'logs' => [
                'icon' => 'mdi mdi-clipboard-text',
            ],
        ],
        'client' => [

        ],
    ],
    'controllers' => [
        'admin'  => [
            \ModulesGarden\TSSGGSModule\Packages\Logs\Http\Admin\Logs::class,
        ],
        'client' => [

        ],
    ],
    'packages'    => [
        'ModuleSettings'
    ],
    'config'      => [
        'colors' => [
            LogTypes::EMERGENCY => [
                'type'   => \ModulesGarden\TSSGGSModule\Core\Components\Enums\Type::DANGER,
                'icon'   => 'alert-triangle',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::ALERT     => [
                'type'   => Type::PRIMARY,
                'icon'   => 'alert-circle-o',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::CRITICAL  => [
                'type'   => Type::DANGER,
                'icon'   => 'alert-polygon',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::ERROR     => [
                'type'   => Type::DANGER,
                'icon'   => 'alert-octagon',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::WARNING   => [
                'type'   => Type::WARNING,
                'icon'   => 'alert-circle',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonWarning::class,
            ],
            LogTypes::NOTICE    => [
                'type'   => Type::INFO,
                'icon'   => 'info',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonInfo::class,
            ],
            LogTypes::INFO      => [
                'type'   => Type::SUCCESS,
                'icon'   => 'information-outline',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonInfo::class,
            ],
            LogTypes::DEBUG     => [
                'type'   => Type::DEFAULT,
                'icon'   => 'bug',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonBasic::class,
            ],
            'total'             => [
                'type'   => Type::DEFAULT,
                'icon'   => 'layers',
                'button' => \ModulesGarden\TSSGGSModule\Components\Button\ButtonBasic::class,
            ],
        ],
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
