<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class UserEdit extends Modal\ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Forms\UserEdit());
    }
}
