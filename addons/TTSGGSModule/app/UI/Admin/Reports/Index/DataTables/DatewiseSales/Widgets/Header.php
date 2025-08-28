<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Widgets;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\ArrayDataProvider\DataProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;


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

        $paid    = Helpers::formatSelectedCurrency($paid);
        $unpaid  = Helpers::formatSelectedCurrency($unpaid);
        $overdue = Helpers::formatSelectedCurrency($overdue);

        $content = <<<CONTENT
                    <p class="report-header">{$this->translate('paid')} <span class="green">$paid </span>{$this->translate('unpaid')} <span class="red">$unpaid </span>{$this->translate('overdue')} <span class="yellow">$overdue</span></p>
CONTENT;

        $this->setType(Color::SECONDARY);
        $this->setText($content);
    }
}