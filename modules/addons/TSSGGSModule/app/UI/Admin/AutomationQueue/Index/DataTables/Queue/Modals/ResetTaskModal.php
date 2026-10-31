<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Modals;

use ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Forms\ResetTaskForm;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalWarning;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ResetTaskModal extends ModalWarning implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ResetTaskForm());
        $this->setTitle($this->translate('title'));
        $this->setContent($this->translate('description'));
    }
}
