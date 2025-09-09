<?php

use ModulesGarden\TSSGGSModule\App\Cron\TestCommand;

return [
    'commands' => function () {
        return [
            new \ModulesGarden\TSSGGSModule\Packages\Scheduler\Models\Command(TestCommand::class, "*/5 * * * *", ['someParam' => 997])
        ];
    }
];
