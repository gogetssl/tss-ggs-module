<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Addon;

use ModulesGarden\TSSGGSModule\Core\Configuration\Data;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AddonControllerInterface;

/**
 * Module configuration wrapper
 */
class Config extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController implements AddonControllerInterface
{
    public function execute($params = [])
    {
        return [
            'name'        => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.systemName'),
            'description' => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.description'),
            'version'     => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
            'author'      => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.author'),
            'fields'      => \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.fields', [])
        ];
    }
}
