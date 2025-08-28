<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing;

use ModulesGarden\TTSGGSModule\Core\Routing\RoutesProviders\DefaultdParameters;
use ModulesGarden\TTSGGSModule\Core\Routing\RoutesProviders\Standard;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class Router
{
    protected array $providers = [];
    protected Route $route;

    public function __construct()
    {
        $this->providers[] = new Standard();
        $this->route       = new RouteNotFound();
    }

    public function find(string $level): ?Route
    {
        foreach ($this->providers as $provider)
        {
            if ($route = $provider->find(Request::getFacadeRoot(), $level))
            {
                $this->route = $route;

                return $this->route;
            }
        }


        return null;
    }

    public function getCurrentRoute(): Route
    {
        return $this->route;
    }
}