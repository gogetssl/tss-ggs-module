<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;


/**
 * @method static get(string $key, mixed $default = null)
 * @method static ip()
 * @method static request() - return request object
 * @method static query() - return query object
 * @method static isSecure()
 * @method static getScheme
 * @link https://laravel.com/api/6.x/Illuminate/Http/Request.html
 */
class Request extends AbstractFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Services\Request::class;
    }
}
