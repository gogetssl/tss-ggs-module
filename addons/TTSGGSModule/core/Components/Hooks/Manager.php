<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Hooks;

class Manager
{
    protected static array $hooks = [];

    public static function register(string $hook, callable $callable, int $priority = 0): void
    {
        if (!array_key_exists($hook, self::$hooks) || array_key_exists($priority, self::$hooks[$hook]))
        {
            self::$hooks[$hook][$priority] = [];
        }

        self::$hooks[$hook][$priority][] = $callable;
    }

    public static function call(string $hook, $data = null): void
    {
        if (!array_key_exists($hook, self::$hooks))
        {
            return;
        }

        ksort(self::$hooks[$hook]);

        foreach (self::$hooks[$hook] as $hooks)
        {
            foreach ($hooks as $callable)
            {
                $callable($data);
            }
        }
    }
}