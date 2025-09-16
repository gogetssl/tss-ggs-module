<?php

namespace ModulesGarden\TSSGGSModule\Components\Modal;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalFormSubmit;

class ModalEdit extends ModalBase
{
    protected $actionModal = false;

 

    protected function initActionButtons()
    {
        $this->addActionButton(
            (new ButtonSuccess())
                ->setTitle($this->translate('submit'))
                ->onClick(new ModalFormSubmit($this))
        );

        $this->addActionButton(
            (new ButtonCancel())
                ->setTitle($this->translate('cancel'))
                ->onClick(new ModalClose($this))
        );
    }
}
