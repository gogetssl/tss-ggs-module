<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Components\Modal\ModalSuccess;

class Modal extends ModalSuccess
{
    public function loadHtml(): void
    {
        $this->addElement(new Form());
    }
}