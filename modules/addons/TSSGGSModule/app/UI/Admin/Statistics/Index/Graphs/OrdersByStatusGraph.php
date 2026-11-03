<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Graphs;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\Graph\GraphDoughnut;
use ModulesGarden\TSSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TSSGGSModule\Components\Graph\Series\SimpleSeries;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class OrdersByStatusGraph extends GraphDoughnut implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $this->options->chart->height = "380px";
        $records = [];
        foreach((new RecordsProvider())->getRecords([]) as $record)
        {
            $records[$record['serviceId']] = $record;
        }

        $counts = [];
        foreach($records as $record)
        {
            $status          = $record['status'] ?: 'Unknown';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        arsort($counts);

        $this->setLabels(array_keys($counts));
        foreach($counts as $count)
        {
            $this->addSeries(new SimpleSeries($count));
        }
    }
}
