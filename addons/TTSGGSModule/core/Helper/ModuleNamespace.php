<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

/**
 * Helper for generating random strings
 */
class ModuleNamespace
{
    public static function isInRootNamespace(string $namespace, int $level = 2, array $elements = []): bool
    {
        $rootNamespace = explode('\\', ModuleConstants::getFullNamespace(...$elements));
        $elementNamespace = explode('\\', $namespace);

        $intersectValues = array_intersect($rootNamespace, $elementNamespace);
        $intersectKeys = array_intersect_key($elementNamespace, $rootNamespace);

        return count($intersectValues) == $level && empty(array_diff($intersectValues, $intersectKeys));
    }

    public static function compareNamespaceStringWithObject(string $namespaceString, object $obj): bool
    {
        return $namespaceString === self::convertNamespaceToString($obj);
    }

    public static function convertNamespaceToString(object $obj): string
    {
        return str_replace('\\', '_', get_class($obj));
    }

    public static function convertStringToNamespace(string $string): string
    {
        return str_replace('_', '\\', $string);
    }
}
