<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing;

class RouteNotFound extends Route
{
    public function __construct()
    {
        $this->name = '404';
    }
}