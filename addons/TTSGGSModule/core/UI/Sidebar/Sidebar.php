<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Sidebar;

use ModulesGarden\TTSGGSModule\Core\Contracts\UI\SidebarItem;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;

class Sidebar
{
    protected array $items = [];
    protected string $name = '';

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addItem(SidebarItem $item)
    {
        $this->items[$item->getName()] = $item;
    }

    public function getItems(): array
    {
        $this->updateItemsOrder();

        return $this->items;
    }

    public function getItemByName(string $itemName):?SidebarItem
    {
        return Arr::get($this->items, $itemName);
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
        uasort($this->items, function(SidebarItem $a, SidebarItem $b) {
            return $a->getOrder() > $b->getOrder();
        });
    }
}