<?php

namespace ModulesGarden\TTSGGSModule\Core\DependencyInjection;

/**
 * Class DependencyInjection
 */
class DependencyInjection
{
    /**
     * @param null $className
     * @param null $methodName
     * @return mixed
     */
    public static function call($className = null, $methodName = null)
    {
        if ($methodName)
        {
            return Container::getInstance()->call("$className@$methodName");
        }

        return Container::getInstance()->make($className);
    }

    /**
     * @param null $className
     * @param null $methodName
     * @return mixed
     */
    public static function create($className = null, $methodName = null)
    {
        return Container::getInstance()->make($className);
    }

    /**
     * @param $className
     */
    public function __invoke($className)
    {
        self::get($className);
    }

    /**
     * @param null $className
     * @param null $methodName
     * @return mixed
     */
    public static function get($className = null, $methodName = null)
    {
        return Container::getInstance()->make($className);
    }
}
