<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Routing;

use ModulesGarden\TSSGGSModule\Core\Routing\Route;

interface RouteProviderInterface
{
    public function find(\Symfony\Component\HttpFoundation\Request $request, string $level) : ?Route;
}