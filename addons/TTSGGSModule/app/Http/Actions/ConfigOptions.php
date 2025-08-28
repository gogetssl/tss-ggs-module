<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Actions;

use ModulesGarden\TTSGGSModule\App\Http\Admin\Home;
use ModulesGarden\TTSGGSModule\Core\UI\ViewConfigOptions;
use ModulesGarden\TTSGGSModule\Core\UI\ViewIntegrationAddon;
use ModulesGarden\TTSGGSModule\Packages\Samples\Http\Admin\Samples;

class ConfigOptions extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Server\ConfigOptions
{
    public function execute($params = null)
    {
        return (new ViewConfigOptions())->addElement(new \ModulesGarden\TTSGGSModule\App\UI\Actions\ConfigOptions());
    }
}
