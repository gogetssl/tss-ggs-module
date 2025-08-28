<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalBase;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms\ShowDataForm;

class ShowDataModal extends ModalBase implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ShowDataForm());
        $this->setTitle($this->translate('title'));
    }
}
