<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalDanger;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms\MenuDeleteForm;

class MenuDeleteModal extends ModalDanger implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new MenuDeleteForm());
        $this->setTitle($this->translate('title'));
    }
}