<?php

namespace ModulesGarden\TSSGGSModule\Components\Modal;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonWarning;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalFormSubmit;

class ModalWarning extends ModalAction
{
    protected $type = self::TYPE_WARNING;

    protected function initActionButtons()
    {
        $this->addActionButton(
            (new ButtonWarning())
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
