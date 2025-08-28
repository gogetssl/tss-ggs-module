<?php


namespace ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\AbstractFacade;

/**
 * @method static get(string $setting, $default = null)
 * @method static delete(string $setting);
 * @method static set(string $setting, $value)
 * @method static save(array $settings = []);
 */
class ModuleSettings extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Services\ModuleSettings::class;
    }
}