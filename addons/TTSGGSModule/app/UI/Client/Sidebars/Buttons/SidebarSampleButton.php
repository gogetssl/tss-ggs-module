<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars\Buttons;

use ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars\Modals\SidebarSampleModal;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;

class SidebarSampleButton extends IconButton
{
    public function loadHtml(): void
    {
        $this->onClick(new ModalLoad(new SidebarSampleModal()));
        $this->displayWithTitle("Open Popup");
        $this->setTitle("qwertyuikol");
    }
}