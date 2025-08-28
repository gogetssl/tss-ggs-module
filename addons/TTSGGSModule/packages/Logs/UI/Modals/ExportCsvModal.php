<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms\ExportCsvForm;

class ExportCsvModal extends ModalEdit implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ExportCsvForm());
        $this->setTitle($this->translate('title'));
    }
}