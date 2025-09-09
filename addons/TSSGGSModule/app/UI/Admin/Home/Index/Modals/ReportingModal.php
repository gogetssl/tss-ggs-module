<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Forms\ReportingForm;
use ModulesGarden\TSSGGSModule\Components\Modal\Modal;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ReportingModal extends Modal implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('sendReport'));
        $this->addElement(new ReportingForm());
    }
}