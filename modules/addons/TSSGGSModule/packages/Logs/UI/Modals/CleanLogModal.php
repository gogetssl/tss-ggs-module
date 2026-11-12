<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\ModalDanger;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms\CleanLogForm;

class CleanLogModal extends ModalDanger implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new CleanLogForm());
        $this->setTitle($this->translate('title'));
        $this->setContent($this->translate('description'));
    }
}
