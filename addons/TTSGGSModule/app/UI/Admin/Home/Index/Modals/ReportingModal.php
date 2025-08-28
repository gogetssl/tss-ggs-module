<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Modals;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Forms\ReportingForm;
use ModulesGarden\TTSGGSModule\Components\Modal\Modal;
use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ReportingModal extends Modal implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('sendReport'));
        $this->addElement(new ReportingForm());
    }
}