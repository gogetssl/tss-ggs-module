<?php

namespace ModulesGarden\TSSGGSModule\Components\Modal;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;

class ModalBase extends Modal
{
    public function __construct()
    {
        parent::__construct();

        $this->initActionButtons();

        $this->setTitle($this->translate('title'));
    }

    protected function initActionButtons()
    {
        $this->addActionButton(
            (new ButtonCancel())
                ->setTitle($this->translate('close'))
                ->onClick(new ModalClose($this))
        );
    }
}
