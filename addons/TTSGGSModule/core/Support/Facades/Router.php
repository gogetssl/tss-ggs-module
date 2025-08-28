<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


/**
 * @method static getCurrentRoute : \ModulesGarden\TTSGGSModule\Core\Routing\Route
 */
class Router extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Router::class;
    }
}
