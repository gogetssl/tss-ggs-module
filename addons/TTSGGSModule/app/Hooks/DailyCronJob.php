<?php

use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\Core\Hook\HookIntegrator;


$hookManager->register(
    function($args) {
        RemoteProduct::synchronize();
    },
    100
);
