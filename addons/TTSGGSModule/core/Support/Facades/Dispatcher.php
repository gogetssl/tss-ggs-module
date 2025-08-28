<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

class Dispatcher extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Events\Dispatcher::class;
    }
}
