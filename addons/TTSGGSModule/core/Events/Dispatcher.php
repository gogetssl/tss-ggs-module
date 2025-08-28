<?php

namespace ModulesGarden\TTSGGSModule\Core\Events;

use Closure;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection\Container;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class Dispatcher extends \Illuminate\Events\Dispatcher
{
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->initialize();
    }

    protected function initialize()
    {
        /**
         * Load available events
         */
        $path   = ModuleConstants::getFullPath('app', 'Config', 'events.php');
        $config = include $path;

        foreach ($config as $event => $listeners)
        {
            foreach ($listeners as $listener)
            {
                $this->listen($event, $listener);
            }
        }
    }

    public function fire($event, $payload = [], $halt = false)
    {
        return $this->dispatch($event, $payload, $halt);
    }

    /**
     * @param $class
     * @param $arguments
     */
    public function queue($class, $arguments)
    {
        $class = implode('@', $this->parseClassCallable($class));

        $this->resolveQueue()->push("$class", serialize($arguments));
    }

    /**
     * @param $class
     * @param $method
     * @override
     * @return Closure
     */
    protected function createQueuedHandlerCallable($class, $method)
    {
        return function() use ($class, $method) {
            $arguments = $this->cloneArgumentsForQueueing(func_get_args());

            if (method_exists($class, 'queue'))
            {
                $this->callQueueMethodOnHandler($class, $method, $arguments);
            }
            else
            {
                $this->resolveQueue()->push("{$class}@{$method}", serialize($arguments));
            }
        };
    }
}
