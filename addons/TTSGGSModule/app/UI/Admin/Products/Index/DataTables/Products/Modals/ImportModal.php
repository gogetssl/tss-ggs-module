<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms\ImportForm;
use ModulesGarden\TTSGGSModule\Components\Modal\Modal;
use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ImportModal extends ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setSize(Modal::SIZE_EXTRA_LARGE);
        $this->addElement(new ImportForm());
    }
}