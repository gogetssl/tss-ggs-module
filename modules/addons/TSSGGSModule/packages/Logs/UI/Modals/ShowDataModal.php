<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\ModalBase;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms\ShowDataForm;

class ShowDataModal extends ModalBase implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ShowDataForm());
        $this->setTitle($this->translate('title'));
    }
}
