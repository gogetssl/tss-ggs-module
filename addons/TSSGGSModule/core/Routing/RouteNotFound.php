<?php

namespace ModulesGarden\TSSGGSModule\Core\Routing;

class RouteNotFound extends Route
{
    public function __construct()
    {
        $this->name = '404';
    }
}