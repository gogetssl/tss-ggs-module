<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class NamespaceConverter
{
    public static function convert($namespace): string
    {
        $namespace = self::removeParts($namespace);
        $chunks    = array_filter(explode('\\', $namespace));
        $chunks    = array_map(function($chunk) {
            return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $chunk)), '_');
        }, $chunks);

        return implode('.', $chunks);
    }

    protected static function removeParts($namespace)
    {
        $partsToRemove = [
            ModuleConstants::getRootNamespace(),
            '\\App\\UI\\',
            'UI\\',
        ];

        return str_replace($partsToRemove, '', $namespace);
    }
}
