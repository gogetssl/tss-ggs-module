<?php

namespace ModulesGarden\TSSGGSModule\Core\Support\Facades;


/**
 * @see \ModulesGarden\TSSGGSModule\Core\Services\Messages
 * @method static alert(string $message)
 * @method static toast(string $message)
 * @method static flash(string $message)
 */
class Messages extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TSSGGSModule\Core\Services\Messages::class;
    }
}
