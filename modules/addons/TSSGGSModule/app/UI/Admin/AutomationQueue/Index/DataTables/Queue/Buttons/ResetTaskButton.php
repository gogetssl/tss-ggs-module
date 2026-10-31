<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Buttons;

use ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Modals\ResetTaskModal;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonWarning;
use ModulesGarden\TSSGGSModule\Core\Components\Action;

class ResetTaskButton extends IconButtonWarning
{
    public function __construct()
    {
        parent::__construct();

        $this->setIcon('restore');
    }

    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->setTitle($this->translate('resetTask'));
        // modalLoad (not modalOpen) - the modal is loaded with the row "id", so read() generates a valid integrity token
        $this->onClick(Action::modalLoad(new ResetTaskModal()));
    }
}
