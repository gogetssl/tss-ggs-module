<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation\Source;

interface TranslationDataInterface
{
    public function getKey(): string;
    public function getReplacements(array $additions = []):array;
}