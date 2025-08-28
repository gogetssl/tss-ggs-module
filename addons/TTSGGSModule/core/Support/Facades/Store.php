<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

class Store extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Store::class;
    }
}