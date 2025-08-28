<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
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
                       ->where('tblproducts.servertype', 'TTSGGSModule')
                       ->select(Capsule::raw('tblinvoiceitems.userid as clientId, SUM(`firstpaymentamount`) as paid'))
                       ->groupBy('tblinvoiceitems.userid')
                       ->get();

        $data = [];

        foreach($rows as $row)
        {
            $paid            = $row->paid;
            $clientId        = $row->clientId;
            $data[$clientId] = Helpers::clientCurrencyToSelectedCurrency($paid, $clientId);
        }

        arsort($data);

        $data = array_slice($data, 0, 5, true);

        foreach($data as $clientId => $paid)
        {
            $link   = Helpers::getAdminAreaClientLink($clientId);
            $record = new Record([$clientId, $link, Helpers::formatSelectedCurrency($paid)]);
            $table->addRecord($record);
        }

        $this->addElement($table);
    }
}