<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms\ImportForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms\ReSyncForm;
use ModulesGarden\TTSGGSModule\Components\Modal\Modal;
use ModulesGarden\TTSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ReSyncModal extends ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('reSync'));
        //$this->setSize(Modal::SIZE_EXTRA_LARGE);
        $this->addElement(new ReSyncForm());
        $this->addElement((new Text())->setText($this->translate('description')));
    }
}