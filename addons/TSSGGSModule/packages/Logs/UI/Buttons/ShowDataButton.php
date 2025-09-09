<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals\ShowDataModal;

class ShowDataButton extends IconButtonEdit
{
    public function loadHtml(): void
    {
        $this->setIcon('info');
        $this->onClick(Action::modalLoad(new ShowDataModal()));
    }
}
