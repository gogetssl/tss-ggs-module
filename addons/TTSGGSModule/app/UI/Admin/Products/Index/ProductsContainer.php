<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ProductsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        //$alert = new Alert();
        //$alert->setText($this->translate("Hello Products!"));
        //$this->addElement($alert);

        $this->addElement(new DataTable());
    }
}