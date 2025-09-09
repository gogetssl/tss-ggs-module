<?php

namespace ModulesGarden\TSSGGSModule\Core\Routing\Middleware\Processors;

use ModulesGarden\TSSGGSModule\Core\Http\Request;
use ModulesGarden\TSSGGSModule\Core\Routing\Route;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

class Controller
{
    public function run(Route $route, Request $request, callable $caller)
    {
        $next = fn($request) => $caller();

        foreach (Config::get('middlewares', []) as $middleware)
        {
            $next = fn($request) => $middleware($request, $next);
        }

        return $next($request);
    }
}