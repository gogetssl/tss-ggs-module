<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\Modal;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\SwitchersForm;

class SwitchersModal extends ModalEdit
{
    public function loadHtml(): void
    {
        $this->addElement(new SwitchersForm());
        $this->setSize(Modal::SIZE_LARGE);
    }
}