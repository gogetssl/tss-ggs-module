<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\Modal;
use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\SwitchersForm;

class SwitchersModal extends ModalEdit
{
    public function loadHtml(): void
    {
        $this->addElement(new SwitchersForm());
        $this->setSize(Modal::SIZE_LARGE);
    }
}