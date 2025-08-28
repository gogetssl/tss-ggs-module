<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Graphs;


use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TTSGGSModule\Components\Graph\GraphArea;
use ModulesGarden\TTSGGSModule\Components\Graph\GraphLine;
use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

use Illuminate\Database\Capsule\Manager as Capsule;

class ProductIncomeGraph extends GraphArea implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $this->options->chart->height = "500px";

        $labels       = [];
        $productNames = [];
        $profit       = [];
        $middleDate   = date("Y-m-", strtotime('now')) . '15';

        for($monthIndex = 5; $monthIndex >= 0; $monthIndex--)
        {
            $timestamp = strtotime($middleDate . ' - ' . $monthIndex . ' months');
            $start     = date("Y-m-", $timestamp) . '01 00:00:00';
            $end       = date("Y-m-t 23:59:59", $timestamp);
            $monthName = date("F", $timestamp);
            $labels[]  = $monthName;

            $recordsProvider = new RecordsProvider();
            $records         = $recordsProvider->getRecords(['fromDate' => $start, 'toDate' => $end]);

            foreach($records as $record)
            {
                $productNames[$record['productId']]        = $record['productName'];
                $profit[$record['productId']][$monthIndex] += $record['grossProfit'];
            }
        }


        //calculate total profit per product

        $totalProfit = [];

        foreach($profit as $productId => $productData)
        {
            foreach($productData as $monthIndex => $monthlyProfit)
            {
                $totalProfit[$productId] += $monthlyProfit;
            }
        }

        arsort($totalProfit);

        //display top 5 products
        $index = 0;

        foreach($totalProfit as $productId => $void)
        {
            $dataSet = new DataSet();
            $dataSet->setTitle($productNames[$productId]);

            for($monthIndex = 5; $monthIndex >= 0; $monthIndex--)
            {
                $productProfit = floatval($profit[$productId][$monthIndex]);
                $productProfit = number_format($productProfit, 2, '.', '');

                $dataSet->addDataItem($productProfit);
            }

            $this->addDataSet($dataSet);
            $index++;

            if($index > 5)
            {
                break;
            }
        }

        $this->setLabels($labels);
    }
}