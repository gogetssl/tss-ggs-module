<?php

$hookManager->register(
    function($args) {
        (new \ModulesGarden\TTSGGSModule\Packages\Logs\Services\AutoPrune())->run();
    },
    100
);
