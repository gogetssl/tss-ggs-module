<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms\ExportCsvForm;

class ExportCsvModal extends ModalEdit implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ExportCsvForm());
        $this->setTitle($this->translate('title'));
    }
}