<?php

use ModulesGarden\TTSGGSModule\Core\Events\Events\ModuleActivated;
use ModulesGarden\TTSGGSModule\Core\Events\Events\PreServerConfigurationLoaded;
use ModulesGarden\TTSGGSModule\Core\Events\Events\ConfigOptionsLoaded;
use function ModulesGarden\TTSGGSModule\Core\listen;

return [
    'bootstrap' => function() {
        listen(ModuleActivated::class, \ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Listeners\ModuleActivated::class);
        listen(PreServerConfigurationLoaded::class, \ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Listeners\PreServerConfigurationLoaded::class);
        listen(ConfigOptionsLoaded::class, \ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Listeners\ModuleActivated::class);

        \ModulesGarden\TTSGGSModule\Core\DependencyInjection\Container::getInstance()->singleton(\ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Services\ModuleSettings::class);
    },
];
