<?php

namespace ModulesGarden\TTSGGSModule\Core\DependencyInjection;

use Illuminate\Contracts\Container\Container as ContainerContract;
use ReflectionParameter;

class Container extends \Illuminate\Container\Container
{
    protected static $instance = null;

    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance(?ContainerContract $container = null)
    {
        self::$instance = $container;
    }

    /**
     * @param $parameters
     * @param array $primitives
     * @return array
     */
    protected function getDependencies($parameters, array $primitives = [])
    {
        $dependencies = [];
        foreach ($parameters as $parameter)
        {
            if ($parameter->isOptional())
            {
                break;
            }

            $dependency = $parameter->getClass();
            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class and
            // we will just bomb out with an error since we have no-where to go.
            if (array_key_exists($parameter->name, $primitives))
            {
                $dependencies[] = $primitives[$parameter->name];
            }
            elseif (is_null($dependency))
            {
                $dependencies[] = $this->resolveNonClass($parameter);
            }
            else
            {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return (array)$dependencies;
    }

    /**
     * Set null value as default parameter when cannot find default value
     * @param ReflectionParameter $parameter
     * @return null
     */
    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable())
        {
            return $parameter->getDefaultValue();
        }

        return null;
    }

    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if (!is_null($concrete = $this->getContextualConcrete('$' . $parameter->name)))
        {
            return $concrete instanceof Closure ? $concrete($this) : $concrete;
        }

        if ($parameter->isDefaultValueAvailable())
        {
            return $parameter->getDefaultValue();
        }

        if ($parameter->hasType() && $parameter->isOptional() && $parameter->getType() instanceof \ReflectionNamedType)
        {
            switch ($parameter->getType()->getName())
            {
                case 'array';
                    return [];
            }
        }

        $this->unresolvablePrimitive($parameter);
    }
}
