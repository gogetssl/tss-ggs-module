<?php

namespace ModulesGarden\TSSGGSModule\Components\Modal;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonClose;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;

class ModalInfo extends ModalBase
{
    protected $actionModal = false;

 

    protected function initActionButtons()
    {
        $this->addActionButton(
            (new ButtonClose())
                ->setTitle($this->translate('close'))
                ->onClick(new ModalClose($this))
        );
    }
}
