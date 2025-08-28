<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\DeleteLogModal;

class DeleteLogButton extends IconButton\IconButtonDelete
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(Action::modalOpen(new DeleteLogModal()));
    }
}
