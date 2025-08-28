<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook\Integration\Models;

class RelatedField
{
    protected string $selector;
    protected array $values;

    public function __construct(string $selector, array $values)
    {
        $this->selector = $selector;
        $this->values   = $values;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}