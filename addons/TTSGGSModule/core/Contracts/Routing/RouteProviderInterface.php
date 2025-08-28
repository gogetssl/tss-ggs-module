<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Routing;

use ModulesGarden\TTSGGSModule\Core\Routing\Route;

interface RouteProviderInterface
{
    public function find(\Symfony\Component\HttpFoundation\Request $request, string $level) : ?Route;
}