<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals\CleanLogModal;

class CleanLogButton extends ButtonDanger
{
    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->setIcon('delete');
        $this->setTitle($this->translate('title'));
        $this->onClick(Action::modalOpen(new CleanLogModal()));
    }
}
