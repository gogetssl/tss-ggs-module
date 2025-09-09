<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets\ImportWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets\ImportWidgetCsv;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets\TemplateDownloadWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\ArrayDataProvider\DataTable as DatewiseSalesDataTable;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\FilterWidget as DatewiseSalesFilterWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\Header as DatewiseSalesHeader;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider\DataTable as ProfitLossDataTable;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\FilterWidget as ProfitLossFilterWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\Header as ProfitLossHeader;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\ArrayDataProvider\DataTable as RenewalDataTable;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Widgets\FilterWidget as RenewFilterWidget;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ImportContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        //$this->addElement(new ImportWidget());

        $tab1 = new Tab();
        $tab1->setTitle($this->translate("single"));
        $tab1->addElement(new ImportWidget());

        $tab2 = new Tab();
        $tab2->setTitle($this->translate("csv"));
        $tab2->addElement(new TemplateDownloadWidget());
        $tab2->addElement(new ImportWidgetCsv());

        $tabs = new TabsWidget();
        $tabs->addTab($tab1);
        $tabs->addTab($tab2);


        $this->addElement($tabs);

    }
}