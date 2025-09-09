<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals\DeleteLogModal;

class DeleteLogButton extends IconButton\IconButtonDelete
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->onClick(Action::modalOpen(new DeleteLogModal()));
    }
}
