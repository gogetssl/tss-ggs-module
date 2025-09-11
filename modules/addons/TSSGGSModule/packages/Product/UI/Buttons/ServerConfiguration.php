<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonInfo;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Product\UI\Modals;

class ServerConfiguration extends ButtonInfo implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('button.title'));
        $this->setIcon('settings');
        $this->onClick((new ModalLoad(new Modals\ServerConfiguration())));
    }
}