<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class Click extends AbstractActionInterface
{
    protected string $selector;

    public function __construct(string $selector)
    {
        $this->selector = $selector;
    }

    public static function byClass(string $className):self
    {
        return new self(".$className");
    }

    public static function byClasses(array $classes):self
    {
        return new self("." . implode('.', $classes));
    }

    public static function byAttribute(string $attributeName, string $attributeValue, string $tag = "", string $operator = "="):self
    {
        return new self("$tag" . "[{$attributeName}{$operator}'{$attributeValue}']");
    }

    public static function byId(string $className):self
    {
        return new self("#$className");
    }

    public static function byComponent(AbstractComponent $component):self
    {
        return self::byId($component->getId());
    }

    public function toArray(): array
    {
        return [
            'action'    => 'click',
            'selector'  => $this->selector
        ];
    }
}