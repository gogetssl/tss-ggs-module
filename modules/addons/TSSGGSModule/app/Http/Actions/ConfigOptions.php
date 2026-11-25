<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Actions;

use ModulesGarden\TSSGGSModule\Core\UI\ViewConfigOptions;

class ConfigOptions extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server\ConfigOptions
{
    public function execute($params = null)
    {
        return (new ViewConfigOptions())->addElement(new \ModulesGarden\TSSGGSModule\App\UI\Actions\ConfigOptions());
    }
}
