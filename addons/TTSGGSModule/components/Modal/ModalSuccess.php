<?php

namespace ModulesGarden\TTSGGSModule\Components\Modal;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalFormSubmit;

class ModalSuccess extends ModalAction
{
    protected $type = self::TYPE_SUCCESS;

    protected function initActionButtons()
    {
        $this->addActionButton(
            (new \ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess())
                ->setTitle($this->translate('submit'))
                ->onClick(new ModalFormSubmit($this))
        );

        $this->addActionButton(
            (new \ModulesGarden\TTSGGSModule\Components\Button\ButtonCancel())
                ->setTitle($this->translate('cancel'))
                ->onClick(new ModalClose($this))
        );
    }
}
