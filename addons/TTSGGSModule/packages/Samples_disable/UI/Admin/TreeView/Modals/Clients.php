<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Clients extends ModalEdit implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms\Clients());
    }
}