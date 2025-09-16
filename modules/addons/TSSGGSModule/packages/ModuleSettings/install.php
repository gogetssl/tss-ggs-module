<?php

use ModulesGarden\TSSGGSModule\Core\Events\Events\ModuleActivated;
use ModulesGarden\TSSGGSModule\Core\Events\Events\PreServerConfigurationLoaded;
use ModulesGarden\TSSGGSModule\Core\Events\Events\ConfigOptionsLoaded;
use function ModulesGarden\TSSGGSModule\Core\listen;

return [
    'bootstrap' => function() {
        listen(ModuleActivated::class, \ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Listeners\ModuleActivated::class);
        listen(PreServerConfigurationLoaded::class, \ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Listeners\PreServerConfigurationLoaded::class);
        listen(ConfigOptionsLoaded::class, \ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Listeners\ModuleActivated::class);

        \ModulesGarden\TSSGGSModule\Core\DependencyInjection\Container::getInstance()->singleton(\ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Services\ModuleSettings::class);
    },
];
