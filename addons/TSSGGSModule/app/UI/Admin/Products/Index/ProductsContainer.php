<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ProductsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $alert = new AlertInfo();
        $alert->setText($this->translate("productsInfo"));
        $this->addElement($alert);

        $this->addElement(new DataTable());
    }
}