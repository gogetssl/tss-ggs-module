<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonInfo;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Product\UI\Modals;

class ServerConfiguration extends ButtonInfo implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('button.title'));
        $this->setIcon('settings');
        $this->onClick((new ModalLoad(new Modals\ServerConfiguration())));
    }
}