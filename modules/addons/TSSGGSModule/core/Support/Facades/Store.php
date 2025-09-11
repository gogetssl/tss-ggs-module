<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;

class Store extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Store::class;
    }
}