<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

/**
 *  @method static getByName(string $item)
 *  @method static array getAll()
 */
class Sidebar extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Sidebar::class;
    }
}