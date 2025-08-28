<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


/**
 * @method static validate(array $data, array $rules, array $customAttributes = [], array $customValues = []);
 */
class Validator extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Validator::class;
    }
}
