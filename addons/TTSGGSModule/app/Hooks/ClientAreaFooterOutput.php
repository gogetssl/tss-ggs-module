<?php

use ModulesGarden\TTSGGSModule\Core\Hook\HookIntegrator;


$hookManager->register(
    function($args) {
        $hookIntegrator = new HookIntegrator($args);

        /**
         * @var $toReturn is a HTML integration code (or null if no integration was made)
         * you can add your code to this var before returning its content,
         * do not overwrite this var!
         */
        $toReturn = $hookIntegrator->getHtmlCode();

        if ($toReturn)
        {
            return $toReturn;
        }
    },
    100
);
