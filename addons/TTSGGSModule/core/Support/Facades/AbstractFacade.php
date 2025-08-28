<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Facades;

use function ModulesGarden\TTSGGSModule\Core\make;

abstract class AbstractFacade
{
    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (method_exists(self::getFacadeRoot(), $method))
        {
            return self::getFacadeRoot()->$method(...$args);
        };

        if (property_exists(self::getFacadeRoot(), $method) && !$args)
        {
            return self::getFacadeRoot()->$method;
        }

        throw new \Exception('Method or property "'.$method . ' does not exist in ' . get_class(self::getFacadeRoot()));
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        $instance = make(static::getFacadeAccessor());

        if (!$instance)
        {
            throw new RuntimeException('Invalid accessor');
        }

        return $instance;
    }

    abstract protected static function getFacadeAccessor(): string;
}
