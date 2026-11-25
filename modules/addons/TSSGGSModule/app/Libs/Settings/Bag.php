<?php

namespace TSSGGSModule\App\Libs\Settings;

/**
 * Values by name, read and written like an array ($bag['name']); a missing name reads as null.
 */
class Bag implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->items) ? $this->items[$name] : $default;
    }

    public function set($name, $value): void
    {
        $this->items[$name] = $value;
    }

    public function offsetExists($name): bool
    {
        return isset($this->items[$name]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->items[$name] ?? null;
    }

    public function offsetSet($name, $value): void
    {
        if($name === null)
        {
            $this->items[] = $value;
        }
        else
        {
            $this->items[$name] = $value;
        }
    }

    public function offsetUnset($name): void
    {
        unset($this->items[$name]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
