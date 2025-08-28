<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Support\Facades;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\AbstractFacade;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\Sidebar as SidebarService;

/**
 * @method static getByName(string $item)
 * @method static array getAll()
 */
class Sidebar extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return SidebarService::class;
    }
}