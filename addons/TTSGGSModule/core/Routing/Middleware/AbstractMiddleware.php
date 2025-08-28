<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing\Middleware;

use ModulesGarden\TTSGGSModule\Core\Http\Request;

abstract class AbstractMiddleware
{
    public function __invoke(Request $request, \Closure $next)/*: \ModulesGarden\TTSGGSModule\Core\Http\Response*/
    {
        return $next($request);
    }
}