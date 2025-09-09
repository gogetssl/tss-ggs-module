<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Modals;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms\ImportForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Forms\PricingForm;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class PricingModal extends ModalEdit implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new PricingForm());
    }
}