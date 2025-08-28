<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\MenuDeleteModal;

class MenuDeleteButton extends DropdownMenuItem
{
    public function loadHtml(): void
    {
        $this->setIcon('delete');
        $this->onClick(Action::modalLoad(new MenuDeleteModal()));
    }
}