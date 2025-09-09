<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals\EditConfigurationModal;

class EditConfiguration extends DropdownMenuItem
{
    public function loadHtml(): void
    {
        $this->setIcon('cog');
        $this->onClick(Action::modalLoad(new EditConfigurationModal()));
    }
}
