<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Modals extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new SuccessModals());
        $this->addElement(new DangerModals());
        $this->addElement(new EditModals());
        $this->addElement(new InfoModals());
    }


}
