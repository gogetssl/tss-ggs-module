<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\EditConfigurationModal;

class EditConfiguration extends DropdownMenuItem
{
    public function loadHtml(): void
    {
        $this->setIcon('cog');
        $this->onClick(Action::modalLoad(new EditConfigurationModal()));
    }
}
