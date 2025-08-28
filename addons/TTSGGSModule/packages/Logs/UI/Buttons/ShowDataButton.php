<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\ShowDataModal;

class ShowDataButton extends IconButtonEdit
{
    public function loadHtml(): void
    {
        $this->setIcon('info');
        $this->onClick(Action::modalLoad(new ShowDataModal()));
    }
}
