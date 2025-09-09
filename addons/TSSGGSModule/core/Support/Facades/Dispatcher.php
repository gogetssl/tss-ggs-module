<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;

class Dispatcher extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Events\Dispatcher::class;
    }
}
