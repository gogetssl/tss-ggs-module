<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Statistics\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use Illuminate\Database\Capsule\Manager as Capsule;

class InvoicesWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $table = new TableSimple;

        $table->setColumns(
            [
                new Column($this->translate('invoiceId')),
                new Column($this->translate('client')),
                new Column($this->translate('status')),
                new Column($this->translate('amount')),
                new Column($this->translate('date')),
            ]
        );

        $rows = Capsule::table('tblinvoices')
                       ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                       ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
                       ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                       ->where('tblinvoiceitems.type', 'Hosting')
                       ->where('tblproducts.servertype', 'TSSGGSModule')
                       ->select(['tblinvoices.*'])
                       ->groupBy('tblinvoices.id')
                       ->orderBy('tblinvoices.id', 'DESC')
                       ->limit(5)
                       ->get();

        foreach($rows as $row)
        {
            $invoiceId      = $row->id;
            $clientId       = $row->userid;
            $amount         = Helpers::clientCurrencyToDefaultCurrency($row->total, $clientId);
            $amountFormated = Helpers::formatDefaultCurrency($amount);
            $status         = $row->status;
            $date           = $row->date;
            $label          = (($status == 'Paid') ? (new LabelSuccess()) : (new LabelDanger()))->setText($status);
            $link           = Helpers::getAdminAreaClientLink($clientId);

            $record = new Record([$invoiceId, $link, $label, $amountFormated, $date]);
            $table->addRecord($record);
        }

        $this->addElement($table);
    }
}