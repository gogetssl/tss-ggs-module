<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Actions;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Home;
use ModulesGarden\TSSGGSModule\Core\UI\ViewConfigOptions;
use ModulesGarden\TSSGGSModule\Core\UI\ViewIntegrationAddon;
use ModulesGarden\TSSGGSModule\Packages\Samples\Http\Admin\Samples;

class ConfigOptions extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server\ConfigOptions
{
    public function execute($params = null)
    {
        return (new ViewConfigOptions())->addElement(new \ModulesGarden\TSSGGSModule\App\UI\Actions\ConfigOptions());
    }
}
