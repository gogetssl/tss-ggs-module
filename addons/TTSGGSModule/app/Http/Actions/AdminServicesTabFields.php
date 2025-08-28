<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Actions;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server\Action;
use ModulesGarden\TTSGGSModule\Packages\Samples\Http\Admin\Samples;

class AdminServicesTabFields extends Action
{
    public function execute($params = null)
    {
        return [Samples::class, 'samplesIntegration'];
    }
}
