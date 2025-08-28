<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Packages\Product\Enums\ConfigSettings;

class ServerConfiguration extends ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('modal.title'));

        $serverConfigForm = Config::get(ConfigSettings::PRODUCT_SERVER_CONFIG_FORM);

        $this->addElement(new $serverConfigForm());
    }
}