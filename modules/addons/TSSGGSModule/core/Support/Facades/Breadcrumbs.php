<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;


use ModulesGarden\TSSGGSModule\Core\UI\Breadcrumbs\Item;

/**
 * @method static add(Item $item);
 * @method static set(array $items);
 * @method static get();
 * @method static delete(int $index);
 * @method static clear();
 * @method static addSuffixToLast(string $text);
 * @method static addPrefixToLast(string $text);
 */
class Breadcrumbs extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Breadcrumbs::class;
    }
}
