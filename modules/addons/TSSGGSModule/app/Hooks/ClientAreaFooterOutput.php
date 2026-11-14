<?php

// only through WHMCS: a direct request gets nothing (no output, no error with the server path)
if(!defined('WHMCS'))
{
    http_response_code(403);
    exit;
}


use ModulesGarden\TSSGGSModule\Core\Hook\HookIntegrator;


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
