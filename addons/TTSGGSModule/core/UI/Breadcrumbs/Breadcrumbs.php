<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Breadcrumbs;

class Breadcrumbs
{
    protected array $items = [];

    public function set(array $items): self
    {
        $this->clear();

        foreach ($items as $item)
        {
            $this->add($item);
        }

        return $this;
    }

    public function delete(int $index): self
    {
        unset($this->items[$index]);

        return $this;
    }

    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * @param string $name
     * @param string $url
     * @param bool $isActive
     * @return $this
     */
    public function add(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function get(): array
    {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function addSuffixToLast(string $text): self
    {
        $lastItem = end($this->items);
        $lastItem->setName($lastItem->getName() . $text);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPrefixToLast(string $text): self
    {
        $lastItem = end($this->items);
        $lastItem->setName($text . $lastItem->getName());
        return $this;
    }
}
