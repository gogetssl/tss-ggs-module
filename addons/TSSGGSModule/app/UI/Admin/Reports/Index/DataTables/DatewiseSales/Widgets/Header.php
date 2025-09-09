<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\ArrayDataProvider\DataProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;


class Header extends Alert implements AjaxComponentInterface, AdminAreaInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('DatewiseSalesHeader');

        $ajaxData            = Request::get('ajaxData');//custom filter values
        $filters['fromDate'] = $ajaxData['fromDate'] ?: '';
        $filters['toDate']   = $ajaxData['toDate'] ?: '';

        $recordsProvider = new RecordsProvider();
        $rows            = $recordsProvider->getRecords($filters);

        $paid    = 0;
        $unpaid  = 0;
        $overdue = 0;

        foreach($rows as $row)
        {
            if($row['paymentStatus'] == 'Paid')
            {
                $paid += $row['salesAmount'];
            }
            if($row['paymentStatus'] == 'Unpaid')
            {
                $unpaid += $row['salesAmount'];
            }
            if($row['paymentStatus'] == 'Overdue')
            {
                $overdue += $row['salesAmount'];
            }
        }

        $paid    = Helpers::formatDefaultCurrency($paid);
        $unpaid  = Helpers::formatDefaultCurrency($unpaid);
        $overdue = Helpers::formatDefaultCurrency($overdue);

        $content = <<<CONTENT
                    <p class="report-header">{$this->translate('paid')} <span class="green">$paid </span>{$this->translate('unpaid')} <span class="red">$unpaid </span>{$this->translate('overdue')} <span class="yellow">$overdue</span></p>
CONTENT;

        $this->setType(Color::SECONDARY);
        $this->setText($content);
    }
}