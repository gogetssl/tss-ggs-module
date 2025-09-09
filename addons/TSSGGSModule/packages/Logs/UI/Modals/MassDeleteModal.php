<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\ModalDanger;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms\MassDeleteForm;

class MassDeleteModal extends ModalDanger implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new MassDeleteForm());
        $this->setTitle($this->translate('title'));
        $this->setContent($this->translate('description'));
    }
}