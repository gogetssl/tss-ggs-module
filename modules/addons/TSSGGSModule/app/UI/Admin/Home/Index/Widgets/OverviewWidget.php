<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\ArrayDataProvider\DataProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Link\Link;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;

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

        $paid    = Helpers::formatDefaultCurrency($paid);
        $unpaid  = Helpers::formatDefaultCurrency($unpaid);
        $overdue = Helpers::formatDefaultCurrency($overdue);

        $content = $this->translate('totalSales') . ' ';
        $content .= $this->translate('paid') . " <span class='green'>{$paid}</span> ";
        $content .= $this->translate('unpaid') . " <span class='red'>{$unpaid}</span> ";
        $content .= $this->translate('overdue') . " <span class='yellow'>{$overdue}</span> ";

        $item = (new Alert())->setType(Color::SECONDARY);
        $item->setText($content);
        $this->addElement($item);

        $class       = ($grossProfit > 0) ? 'green' : 'red';
        $grossProfit = Helpers::formatDefaultCurrency($grossProfit);
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
                ->setUrl('addonmodules.php?module=TSSGGSModule&mg-page=statistics')
                ->setSlot('class', 'lu-btn lu-btn--primary mt30_custom')

        );
    }
}