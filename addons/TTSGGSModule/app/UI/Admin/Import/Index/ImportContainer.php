<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Widgets\ImportWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Widgets\ImportWidgetCsv;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Widgets\TemplateDownloadWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\ArrayDataProvider\DataTable as DatewiseSalesDataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\FilterWidget as DatewiseSalesFilterWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets\Header as DatewiseSalesHeader;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider\DataTable as ProfitLossDataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\FilterWidget as ProfitLossFilterWidget;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Widgets\Header as ProfitLossHeader;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\ArrayDataProvider\DataTable as RenewalDataTable;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Widgets\FilterWidget as RenewFilterWidget;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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