<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\App\Http\Admin\Products as ProductsController;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ProductsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $session = $_SESSION[ProductsController::SESSION] ?? [];
        unset($_SESSION[ProductsController::SESSION]);
        if(!empty($session['error']))
        {
            $this->addElement((new AlertDanger())->setText($session['error']));
        }
        if(!empty($session['notice']))
        {
            $notice = $session['notice'];
            $this->addElement((new AlertSuccess())->setText($this->translate('notice_' . $notice['key'], ['product' => $notice['product'], 'detail' => $notice['detail'] !== '' ? $notice['detail'] : '-'])));
        }

        $alert = new AlertInfo();
        $alert->setText($this->translate("productsInfo"));
        $this->addElement($alert);

        $this->addElement(new DataTable());
    }
}