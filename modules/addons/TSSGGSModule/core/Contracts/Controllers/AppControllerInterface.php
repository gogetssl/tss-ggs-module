<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Controllers;

interface AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params);
}
