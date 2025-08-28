<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Sidebar;

use ModulesGarden\TTSGGSModule\Core\Contracts\UI\SidebarItem;

class Item implements SidebarItem
{
    protected string $name = '';
    protected string $url = '';
    protected string $target = '';
    protected string $icon = '';
    protected int $order = 0;
    protected bool $active = false;

    public function __construct(string $name, string $url = '', int $order = 0)
    {
        $this->setName($name);
        $this->setUrl($url);
        $this->setOrder($order);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}