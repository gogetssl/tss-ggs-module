<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

/**
 * @method static call(object $obj, string $name)
 */
class Binder extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Binder::class;
    }
}
