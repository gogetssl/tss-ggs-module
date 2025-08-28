<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\ExportCsvModal;

class ExportCsvButton extends DropdownMenuItem
{
    public function loadHtml(): void
    {
        $this->setIcon('upload');
        $this->onClick(Action::modalLoad(new ExportCsvModal()));
    }
}