<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Graphs;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TTSGGSModule\Components\Graph\GraphArea;
use ModulesGarden\TTSGGSModule\Components\Graph\GraphLine;
use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;

class TotalIncomeGraph extends GraphArea implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $this->options->chart->height = "500px";

        $labels   = [];
        $dataSet1 = new DataSet();
        $dataSet1->setTitle($this->translate('IncomeGraphSetTitle', ['currencySymbol' => Helpers::getCurrencySymbol()]));

        $middleDate = date("Y-m-", strtotime('now')) . '15';

        for($i = 5; $i >= 0; $i--)
        {
            $timestamp = strtotime($middleDate . ' - ' . $i . ' months');
            $start     = date("Y-m-", $timestamp) . '01 00:00:00';
            $end       = date("Y-m-t 23:59:59", $timestamp);
            $monthName = date("F", $timestamp);
            $labels[]  = $monthName;

            $recordsProvider = new RecordsProvider();
            $records         = $recordsProvider->getRecords(['fromDate' => $start, 'toDate' => $end]);
            $profit          = 0;

            foreach($records as $record)
            {
                $profit += $record['grossProfit'];
            }

            $profit = number_format($profit, 2, '.', '');

            $dataSet1->addDataItem($profit);
        }


        $this->setLabels($labels);
        $this->addDataSet($dataSet1);
    }
}