<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Forms\FilterForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\FilterWidget as DatewiseSalesFilterWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\Header as DatewiseSalesHeader;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\Header as ProfitLossHeader;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Widgets\FilterWidget as RenewFilterWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\FilterWidget as ProfitLossFilterWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\ArrayDataProvider\DataTable as RenewalDataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\ArrayDataProvider\DataTable as DatewiseSalesDataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider\DataTable as ProfitLossDataTable;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class ReportsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        //$alert = new Alert();
        //$alert->setText($this->translate("Hello Reports!"));
        //$this->addElement($alert);

        $tab1 = new Tab();
        $tab1->setTitle($this->translate("renewalReport"));
        $tab1->addElement(new RenewFilterWidget());
        $tab1->addElement(new RenewalDataTable());


        $tab2 = new Tab();
        $tab2->setTitle($this->translate("datewiseSalesReport"));
        $tab2->addElement(new DatewiseSalesHeader());
        $tab2->addElement(new DatewiseSalesFilterWidget());
        $tab2->addElement(new DatewiseSalesDataTable());

        $tab3 = new Tab();
        $tab3->setTitle($this->translate("profitLossReport"));
        $tab3->addElement(new ProfitLossHeader());
        $tab3->addElement(new ProfitLossFilterWidget());
        $tab3->addElement(new ProfitLossDataTable());

        $tabs = new TabsWidget();
        $tabs->addTab($tab1);
        $tabs->addTab($tab2);
        $tabs->addTab($tab3);

        $this->addElement($tabs);
    }
}