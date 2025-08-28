<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Breadcrumbs;

use JsonSerializable;

class Item implements JsonSerializable
{
    protected $name;
    protected $url;

    public function __construct(string $name, string $url = '')
    {
        $this->name = $name;
        $this->url  = $url;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'url'  => $this->url,
        ];
    }
}