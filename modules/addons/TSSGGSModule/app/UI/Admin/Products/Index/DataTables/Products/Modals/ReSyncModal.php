<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms\ImportForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Forms\ReSyncForm;
use ModulesGarden\TSSGGSModule\Components\Modal\Modal;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

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