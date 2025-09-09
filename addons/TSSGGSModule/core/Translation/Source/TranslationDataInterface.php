<?php

namespace ModulesGarden\TSSGGSModule\Core\Translation\Source;

interface TranslationDataInterface
{
    public function getKey(): string;
    public function getReplacements(array $additions = []):array;
}