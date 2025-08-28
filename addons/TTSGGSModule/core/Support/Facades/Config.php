<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

/**
 * @method static get(string $name, $default = null)
 */
class Config extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Config::class;
    }
}
