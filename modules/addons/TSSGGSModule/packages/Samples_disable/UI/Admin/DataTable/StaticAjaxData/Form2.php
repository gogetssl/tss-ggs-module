<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Form2 extends AbstractForm implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->onSubmit((new PassAjaxData((new DataTable())->getId())));
    }

    public function loadData(): void
    {
        $pre = new PreBlock();
        $pre->setContent(print_r($_REQUEST, true));
        $this->addElement($pre);
    }
}