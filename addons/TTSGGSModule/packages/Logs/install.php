<?php

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Type;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection\Container;
use ModulesGarden\TTSGGSModule\Packages\Logs\Enums\LogTypes;
use ModulesGarden\TTSGGSModule\Packages\Logs\Listeners\ModuleActivated;
use ModulesGarden\TTSGGSModule\Packages\Logs\Listeners\ModuleUpgraded;
use ModulesGarden\TTSGGSModule\Packages\Logs\Services\Logs;
use function ModulesGarden\TTSGGSModule\Core\listen;

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
            \ModulesGarden\TTSGGSModule\Packages\Logs\Http\Admin\Logs::class,
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
                'type'   => \ModulesGarden\TTSGGSModule\Core\Components\Enums\Type::DANGER,
                'icon'   => 'alert-triangle',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::ALERT     => [
                'type'   => Type::PRIMARY,
                'icon'   => 'alert-circle-o',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::CRITICAL  => [
                'type'   => Type::DANGER,
                'icon'   => 'alert-polygon',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::ERROR     => [
                'type'   => Type::DANGER,
                'icon'   => 'alert-octagon',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonDanger::class,
            ],
            LogTypes::WARNING   => [
                'type'   => Type::WARNING,
                'icon'   => 'alert-circle',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonWarning::class,
            ],
            LogTypes::NOTICE    => [
                'type'   => Type::INFO,
                'icon'   => 'info',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonInfo::class,
            ],
            LogTypes::INFO      => [
                'type'   => Type::SUCCESS,
                'icon'   => 'information-outline',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonInfo::class,
            ],
            LogTypes::DEBUG     => [
                'type'   => Type::DEFAULT,
                'icon'   => 'bug',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonBasic::class,
            ],
            'total'             => [
                'type'   => Type::DEFAULT,
                'icon'   => 'layers',
                'button' => \ModulesGarden\TTSGGSModule\Components\Button\ButtonBasic::class,
            ],
        ],
    ],
    'bootstrap'   => function() {
//        $oClass = new \ReflectionClass(\ModulesGarden\TTSGGSModule\Packages\Logs\Enums\LogTypes::class);
//        foreach ($oClass->getConstants() as $type)
//        {
//            call_user_func([\ModulesGarden\TTSGGSModule\Packages\Logs\Support\Facades\Logger::class, $type], $type. '{adawd}', ['adawd' => $type]);
//        }

        \ModulesGarden\TTSGGSModule\Core\Hook\HookManager::create(__DIR__, true);
        Container::getInstance()->singleton(Logs::class);
        listen(\ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleActivated::class, ModuleActivated::class);
        listen(\ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleUpgraded::class, ModuleUpgraded::class);
    },
];
