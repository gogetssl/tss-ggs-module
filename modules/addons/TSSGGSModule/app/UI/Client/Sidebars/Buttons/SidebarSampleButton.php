<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\Buttons;

use ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\Modals\SidebarSampleModal;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

class SidebarSampleButton extends IconButton
{
    public function loadHtml(): void
    {
        $this->onClick(new ModalLoad(new SidebarSampleModal()));
        $this->displayWithTitle("Open Popup");
        $this->setTitle("qwertyuikol");
    }
}