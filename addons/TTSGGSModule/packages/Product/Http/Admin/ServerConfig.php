<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Http\Admin;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Helper;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Server;
use ModulesGarden\TTSGGSModule\Packages\Product\Enums\ConfigSettings;
use function ModulesGarden\TTSGGSModule\Core\Helper;

class ServerConfig extends AbstractController implements AdminAreaInterface
{
    public function index()
    {
        if (!Config::get(ConfigSettings::PRODUCT_SERVER_CONFIG_FORM)) {
            return;
        }

        $serverId = Request::get('id', null);
        if (is_null($serverId)) {
            return;
        }

        $server = Server::findOrFail($serverId);
        if (ModuleConstants::getModuleName() !== $server->type) {
            return;
        }

        return Helper\viewIntegrationAddon()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Product\UI\Buttons\ServerConfiguration::class);
    }
}
