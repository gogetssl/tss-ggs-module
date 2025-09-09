<?php

$hookManager->register(
    function($args) {
        (new \ModulesGarden\TSSGGSModule\Packages\Logs\Services\AutoPrune())->run();
    },
    100
);
