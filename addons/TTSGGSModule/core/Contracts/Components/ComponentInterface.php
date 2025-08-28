<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Components;

use JsonSerializable;

interface ComponentInterface extends JsonSerializable
{
    public static function getComponentName(): string;
    public static function getComponentTemplateName(): string;

    public function getId();

    public function loadHtml(): void;

    public function buildHtml(): void;

    public function toArray(): array;
}
