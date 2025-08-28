<?php

namespace ModulesGarden\TTSGGSModule\Core\Data;

use ArrayAccess;
use Exception;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;

class Container implements ArrayAccess
{
    protected ?bool $canSet = null;
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->createFrom($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function createFrom(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return Arr::get($this->data, $name, $default);
    }

    public function __invoke($name, $default = null)
    {
        return $this->get($name, $default);
    }

    /**
     * @param array $names
     * @param $default
     * @return array
     */
    public function getMany(array $names, $default = null): array
    {
        $names = array_flip($names);
        array_walk($names, function(&$value, $name) use ($default) {
            $value = $this->get($name, $default);
        });

        return $names;
    }

    public function all(): array
    {
        return $this->data;
    }


    /**
     * @param $name
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($name): bool
    {
        return Arr::exists($this->data, $name);
    }

    /**
     * @param $name
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($name): mixed
    {
        return $this->get($name);
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->checkIfCanSet();

        $this->set($offset, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value): self
    {
        Arr::set($this->data, $name, $value);

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function push($name, $value): self
    {
        if (Arr::exists($this->data, $name) && !is_array(Arr::get($this->data, $name)))
        {
            Arr::set($this->data, $name, [Arr::get($this->data, $name)]);
        }
        else
        {
            $this->set($name, $value);
        }


        return $this;
    }

    /**
     * @param $offset
     * @return void
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        $this->checkIfCanSet();

        $this->delete($offset);
    }

    public function delete($name): self
    {
        Arr::forget($this->data, $name);

        return $this;
    }

    protected function checkIfCanSet()
    {
        if ($this->canSet === false)
        {
            throw new Exception('Cannot set value in ' . get_class($this));
        }
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function merge(Container $container): self
    {
        $this->data = array_merge($this->data, $container->data);

        return $this;
    }
}
