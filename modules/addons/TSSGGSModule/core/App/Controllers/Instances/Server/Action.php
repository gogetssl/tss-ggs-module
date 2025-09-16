<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController;

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
