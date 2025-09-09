<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;


use ModulesGarden\TSSGGSModule\Core\Contracts\UI\MenuItem;

/**
 *  @method static addItem(MenuItem $item)
 *  @method static getItems(): array
 *  @method static hasItems(): bool
 *  @method static removeItem(string $name)
 */
class Menu extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Menu::class;
    }
}
