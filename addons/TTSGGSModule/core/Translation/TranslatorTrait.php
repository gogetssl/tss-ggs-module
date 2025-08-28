<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation;

use ModulesGarden\TTSGGSModule\Core\Helper\ModuleNamespace;

/**
 * Trait ElementsTrait
 */
trait TranslatorTrait
{
    protected function translate($key, $replace = [], $raw = false)
    {
        return \ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator::getBasedOnNamespaces(
            $raw ?: [get_class($this), ...$this->findBaseParents(get_class($this))],
            $key,
            $replace
        );
    }

    protected function findBaseParents(string $componentClass):array
    {
        return (array)array_filter(array_values(class_parents($componentClass)), function($namespace) {
            return ModuleNamespace::isInRootNamespace($namespace, 3, ['Components']);
        });
    }
}
