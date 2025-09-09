<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Support\Facades;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\AbstractFacade;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\Sidebar as SidebarService;

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