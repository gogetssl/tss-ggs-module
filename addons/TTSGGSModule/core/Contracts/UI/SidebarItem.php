<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\UI;

interface SidebarItem
{
    public function setName(string $name): void;

    public function getName(): string;

    public function setUrl(string $url): void;

    public function getUrl(): ?string;

    public function setIcon(string $icon): void;

    public function getIcon(): ?string;

    public function setOrder(int $order): self;

    public function getOrder(): int;
}