<?php

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\Core\Hook\HookIntegrator;


$hookManager->register(
    function($args) {
        RemoteProduct::synchronize();
    },
    100
);
