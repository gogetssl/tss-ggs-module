<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Addon;

use ModulesGarden\TTSGGSModule\Core\Configuration\Data;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AddonControllerInterface;

/**
 * Module configuration wrapper
 */
class Config extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\AddonController implements AddonControllerInterface
{
    public function execute($params = [])
    {
        return [
            'name'        => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.systemName'),
            'description' => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.description'),
            'version'     => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.version'),
            'author'      => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.author'),
            'fields'      => \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.fields', [])
        ];
    }
}
