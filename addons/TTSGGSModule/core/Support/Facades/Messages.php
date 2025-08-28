<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


/**
 * @see \ModulesGarden\TTSGGSModule\Core\Services\Messages
 * @method static alert(string $message)
 * @method static toast(string $message)
 * @method static flash(string $message)
 */
class Messages extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Messages::class;
    }
}
