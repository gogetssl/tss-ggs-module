<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals;

use ModulesGarden\TTSGGSModule\Components\Modal;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class UserCreate extends Modal\ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Forms\UserCreate());
    }
}
