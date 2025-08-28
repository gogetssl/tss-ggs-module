<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


use ModulesGarden\TTSGGSModule\Core\Contracts\UI\MenuItem;

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
        return \ModulesGarden\TTSGGSModule\Core\Services\Menu::class;
    }
}
