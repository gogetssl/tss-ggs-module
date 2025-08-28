<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


class Smarty extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Smarty::class;
    }
}
