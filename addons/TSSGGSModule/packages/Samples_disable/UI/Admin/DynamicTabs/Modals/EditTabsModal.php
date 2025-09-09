<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Modals;

use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Forms\EditTabsForm;

class EditTabsModal extends ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Edit Tabs');
        $this->addElement(new EditTabsForm());
    }
}