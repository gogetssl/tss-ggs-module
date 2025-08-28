<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation\Models;

use ModulesGarden\TTSGGSModule\Core\Translation\Source\TranslationDataInterface;

class TranslationData implements TranslationDataInterface
{
    protected string $key;
    protected array $replacements = [];

    public function __construct(string $key, array $replacements = [])
    {
        $this->key          = $key;
        $this->replacements = $replacements;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function addReplacement(string $name, string $value):self
    {
        $this->replacements[$name] = $value;

        return $this;
    }

    public function getReplacements(array $additions = []):array
    {
        return array_merge($this->replacements, $additions);
    }
}