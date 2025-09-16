<?php

namespace ModulesGarden\TSSGGSModule\Core\Translation;

use ModulesGarden\TSSGGSModule\Core\Helper\ModuleNamespace;

/**
 * Trait ElementsTrait
 */
trait TranslatorTrait
{
    protected function translate($key, $replace = [], $raw = false)
    {
        return \ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator::getBasedOnNamespaces(
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
