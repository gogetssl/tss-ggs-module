<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Menu;

use ModulesGarden\TSSGGSModule\Core\Contracts\UI\MenuItem;

abstract class AbstractItemContainer
{
    protected array $items = [];

    public function addItem(MenuItem $item)
    {
        $this->items[$item->getName()] = $item;
    }

    public function getItems(): array
    {
        $this->updateItemsOrder();

        return $this->items;
    }

    public function hasItems(): bool
    {
        return (bool)$this->items;
    }

    public function removeItem(string $name)
    {
        unset($this->items[$name]);
    }

    protected function updateItemsOrder()
    {
        uasort($this->items, function(MenuItem $a, MenuItem $b) {
            return $a->getOrder() > $b->getOrder();
        });
    }
}