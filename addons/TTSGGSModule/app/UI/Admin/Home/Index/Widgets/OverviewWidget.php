<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider\DataProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TTSGGSModule\Components\Link\Link;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;

class OverviewWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('format-list-bulleted');

        $filters['fromDate'] = date('Y-m-d', strtotime('-1 month'));
        $filters['toDate']   = date('Y-m-d');

        $recordsProvider = new RecordsProvider();
        $records         = $recordsProvider->getRecords($filters);

        $paid            = 0;
        $unpaid          = 0;
        $overdue         = 0;
        $grossProfit     = 0;
        $issuedLastMonth = 0;

        foreach($records as $record)
        {
            if($record['paymentStatus'] == 'Paid')
            {
                $paid += $record['salesAmount'];
            }
            if($record['paymentStatus'] == 'Unpaid')
            {
                $unpaid += $record['salesAmount'];
            }
            if($record['paymentStatus'] == 'Overdue')
            {
                $overdue += $record['salesAmount'];
            }

            $grossProfit += $record['grossProfit'];
            $issuedLastMonth++;
        }

        $paid    = Helpers::formatSelectedCurrency($paid);
        $unpaid  = Helpers::formatSelectedCurrency($unpaid);
        $overdue = Helpers::formatSelectedCurrency($overdue);

        $content = $this->translate('totalSales') . ' ';
        $content .= $this->translate('paid') . " <span class='green'>{$paid}</span> ";
        $content .= $this->translate('unpaid') . " <span class='red'>{$unpaid}</span> ";
        $content .= $this->translate('overdue') . " <span class='yellow'>{$overdue}</span> ";

        $item = (new Alert())->setType(Color::SECONDARY);
        $item->setText($content);
        $this->addElement($item);

        $class       = ($grossProfit > 0) ? 'green' : 'red';
        $grossProfit = Helpers::formatSelectedCurrency($grossProfit);
        $content     = $this->translate('grossProfit') . " <span class='{$class}'>{$grossProfit}</span>";

        $item = (new Alert())->setType(Color::SECONDARY);
        $item->setText($content);
        $this->addElement($item);

        $content = $this->translate('issuedLastMonth') . " <strong>{$issuedLastMonth}</strong>";

        $item = (new Alert())->setType(Color::SECONDARY);
        $item->setText($content);
        $this->addElement($item);

        $recordsProvider = new RecordsProvider();
        $records         = $recordsProvider->getRecords(['renewalPeriod' => 'next_90']);
        $expireIn90Days  = count($records);

        $content = $this->translate('expireIn90Days') . " <strong>{$expireIn90Days}</strong>";


        $item = (new Alert())->setType(Color::SECONDARY);
        $item->setText($content);
        $this->addElement($item);


        $this->addElement(
            (new Link())
                ->setTitle($this->translate('statistics'))
                ->setUrl('addonmodules.php?module=TTSGGSModule&mg-page=statistics')
                ->setSlot('class', 'lu-btn lu-btn--primary mt30_custom')

        );
    }
}