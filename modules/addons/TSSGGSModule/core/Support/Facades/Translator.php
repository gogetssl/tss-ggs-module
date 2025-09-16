<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;

use ModulesGarden\TSSGGSModule\Core\Services\Translator as TranslatorService;


/**
 * @method static get($key, array $replace = [], $locale = null, $fallback = true)
 */
class Translator extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return TranslatorService::class;
    }
}
