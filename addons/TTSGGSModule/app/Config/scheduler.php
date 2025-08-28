<?php

use ModulesGarden\TTSGGSModule\App\Cron\TestCommand;

return [
    'commands' => function () {
        return [
            new \ModulesGarden\TTSGGSModule\Packages\Scheduler\Models\Command(TestCommand::class, "*/5 * * * *", ['someParam' => 997])
        ];
    }
];
