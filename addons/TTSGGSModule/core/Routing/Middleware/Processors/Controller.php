<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing\Middleware\Processors;

use ModulesGarden\TTSGGSModule\Core\Http\Request;
use ModulesGarden\TTSGGSModule\Core\Routing\Route;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

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