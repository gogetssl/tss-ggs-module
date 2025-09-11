<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;

/**
 * @method static get(string $name, $default = null)
 */
class Config extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Config::class;
    }
}
