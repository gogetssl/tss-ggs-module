<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Controllers;

interface AppControllerInterface
{
    public function getControllerInstanceClass($callerName, $params);
}
