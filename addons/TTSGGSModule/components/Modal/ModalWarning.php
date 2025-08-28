<?php

namespace ModulesGarden\TTSGGSModule\Components\Modal;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonWarning;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalFormSubmit;

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
