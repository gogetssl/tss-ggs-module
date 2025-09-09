<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Actions;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server\Action;
use ModulesGarden\TSSGGSModule\Packages\Samples\Http\Admin\Samples;

class AdminServicesTabFields extends Action
{
    public function execute($params = null)
    {
        return [Samples::class, 'samplesIntegration'];
    }
}
