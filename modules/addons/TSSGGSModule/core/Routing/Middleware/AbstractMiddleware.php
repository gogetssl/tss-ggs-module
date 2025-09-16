<?php

namespace ModulesGarden\TSSGGSModule\Core\Routing\Middleware;

use ModulesGarden\TSSGGSModule\Core\Http\Request;

abstract class AbstractMiddleware
{
    public function __invoke(Request $request, \Closure $next)/*: \ModulesGarden\TSSGGSModule\Core\Http\Response*/
    {
        return $next($request);
    }
}