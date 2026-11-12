<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Graphs;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared\RecordsProvider;
use ModulesGarden\TSSGGSModule\Components\Graph\GraphBar;
use ModulesGarden\TSSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TSSGGSModule\Components\Graph\Series\SimpleSeries;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ExpiringCertificatesGraph extends GraphBar implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $this->options->chart->height = "380px";
        $records = [];
        foreach((new RecordsProvider())->getRecords([]) as $record)
        {
            $records[$record['serviceId']] = $record;
        }

        $today   = date('Y-m-d');
        $buckets = ['expired' => 0, 'd30' => 0, 'd60' => 0, 'd90' => 0];

        foreach($records as $record)
        {
            $expires = $record['expirationDate'];
            if($record['serviceStatus'] !== 'Active' || !$expires || $expires === '-')
            {
                continue;
            }

            if($expires < $today)
            {
                $buckets['expired']++;
            }
            elseif($expires <= date('Y-m-d', strtotime('+30 day')))
            {
                $buckets['d30']++;
            }
            elseif($expires <= date('Y-m-d', strtotime('+60 day')))
            {
                $buckets['d60']++;
            }
            elseif($expires <= date('Y-m-d', strtotime('+90 day')))
            {
                $buckets['d90']++;
            }
        }

        $this->setLabels([
            $this->translate('expired'), $this->translate('days30'), $this->translate('days60'), $this->translate('days90'),
        ]);

        $dataSet = new DataSet();
        $dataSet->setTitle($this->translate('certificates'))->setData(array_values($buckets));
        $this->addDataSet($dataSet);
    }
}
