<?php

return [
    'menu'        => [
        'admin'  => [
            'samples' => [
                'icon'    => 'home',
                'submenu' => [
                    'BasicElements'   => [
                        'controller' => 'basicElements',
                    ],
                    'ComplexElements' => [
                        'controller' => 'complexElements',
                    ],
                    'Forms'           => [
                        'controller' => 'forms',
                    ],
                    'DataTable'       => [
                        'controller' => 'DataTable',
                    ],
                    'Graphs'          => [
                        'controller' => 'Graphs',
                    ],
                    'KanbanBoard'     => [
                        'controller' => 'KanbanBoard',
                    ],
                    'AdaptiveTwoColumnsContainer' => [
                        'controller' => 'AdaptiveTwoColumnsContainer'
                    ],
                    'TreeView'                    => [
                        'controller' => 'TreeView'
                    ],
                    'Notifications' => [
                        'controller' => 'Notifications'
                    ],
                    'HintsBox' => [
                        'controller' => 'HintsBox'
                    ],
                    'AjaxSearch' => [
                        'controller' => 'AjaxSearch'
                    ],
                    'AddReloadableDropdowns' => [
                        'controller' => 'addReloadableDropdowns'
                    ],
                    'OnOffSwitchers' => [
                        'controller' => 'onOffSwitchers'
                    ],
                    'ProgressBars' => [
                        'controller' => 'progressBars'
                    ],
                    'CollapsableBox' => [
                        'controller' => 'collapsableBox'
                    ],
                    'ComponentActions' => [
                        'controller' => 'componentActions'
                    ],
                    'CopyToTargetComponent' => [
                        'controller' => 'copyToTargetComponent'
                    ],
                    'ElementsListComponent' => [
                        'controller' => 'elementsListComponent'
                    ],
                    'TicketRepliesComponent' => [
                        'controller' => 'ticketRepliesComponent'
                    ],
                    'DynamicTabs' => [
                        'controller' => 'dynamicTabs'
                    ]
                ],
            ],
        ],
        'client' => [

        ],
    ],
    'packages'    => [
        'ModuleSettings'
    ],
    'controllers' => [
        'admin'  => [
            \ModulesGarden\TTSGGSModule\Packages\Samples\Http\Admin\Samples::class,
        ],
        'client' => [

        ],
    ],
    'bootstrap'   => function() {
    },
];
