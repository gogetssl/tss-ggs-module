<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;


/**
 * @method static getCurrentRoute : \ModulesGarden\TSSGGSModule\Core\Routing\Route
 */
class Router extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Router::class;
    }
}
