<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use Illuminate\Database\Capsule\Manager as Capsule;

class ResellersWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $table = new TableSimple;

        $table->setColumns(
            [
                new Column($this->translate('clientId')),
                new Column($this->translate('client')),
                new Column($this->translate('paid')),
            ]
        );

        $rows = Capsule::table('tblinvoiceitems')
                       ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
                       ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                       ->where('tblinvoiceitems.type', 'Hosting')
                       ->where('tblproducts.servertype', 'TSSGGSModule')
                       ->select(Capsule::raw('tblinvoiceitems.userid as clientId, SUM(`firstpaymentamount`) as paid'))
                       ->groupBy('tblinvoiceitems.userid')
                       ->get();

        $data = [];

        foreach($rows as $row)
        {
            $paid            = $row->paid;
            $clientId        = $row->clientId;
            $data[$clientId] = Helpers::clientCurrencyToDefaultCurrency($paid, $clientId);
        }

        arsort($data);

        $data = array_slice($data, 0, 5, true);

        foreach($data as $clientId => $paid)
        {
            $link   = Helpers::getAdminAreaClientLink($clientId);
            $record = new Record([$clientId, $link, Helpers::formatDefaultCurrency($paid)]);
            $table->addRecord($record);
        }

        $this->addElement($table);
    }
}