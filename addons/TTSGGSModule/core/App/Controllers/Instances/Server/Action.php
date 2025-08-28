<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\AddonController;

abstract class Action extends AddonController
{
    public function runExecuteProcess($params = null)
    {
        try
        {
            return parent::runExecuteProcess($params);
        }
        catch (\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
}
