<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Chart;
use TSSGGSModule\App\Kernel\Kernel;
use TSSGGSModule\App\Kernel\Layout;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Libs\Billing\SalesReport;
use WHMCS\Database\Capsule;

/**
 * Admin page "Statistics": five charts (income, income per product, expiring certificates, orders by status,
 * revenue by brand) and two short lists (best clients, latest invoices).
 * The page itself is quick; every chart is loaded on its own after the page is shown (action "chart"), because
 * each one goes through all the orders. Templates: resources/templates/admin/statistics.
 */
class Statistics extends Page
{
    const LANG = 'admin.statistics.index';

    /** chart name => [title key, icon], in the order of the page (two per row) */
    const CHARTS = [
        'totalIncome'    => ['widgets.total_income_widget.title', 'trending-up'],
        'productIncome'  => ['widgets.product_income_widget.title', 'chart-bar'],
        'expiring'       => ['widgets.expiring_certificates_widget.title', 'calendar-month-outline'],
        'ordersByStatus' => ['widgets.orders_by_status_widget.title', 'chart-bar'],
        'revenueByBrand' => ['widgets.revenue_by_provider_widget.title', 'trending-up'],
    ];

    const TOP_PRODUCTS = 5;

    public function index()
    {
        $charts = [];
        foreach(self::CHARTS as $name => [$title, $icon])
        {
            $charts[] = ['name' => $name, 'title' => $this->t($title), 'icon' => $icon];
        }

        return $this->view('admin/statistics/index', [
            'showInfo' => !GeneralSettings::enabled('hideInfoMessages'),
            'charts'   => $charts,
            'clients'  => $this->bestClients(),
            'invoices' => $this->latestInvoices(),
        ]);
    }

    /** GET name: one chart, as the fragment that replaces its spinner */
    public function chart()
    {
        $name = $this->request->oneOf('name', array_keys(self::CHARTS));
        if($name === '')
        {
            return Response::fragment(Layout::notFound($this), 404);
        }

        return $this->fragment('admin/partials/chart', ['options' => json_encode($this->$name())]);
    }

    /** Gross profit of each of the last six months */
    protected function totalIncome(): array
    {
        $profit = [];
        foreach($this->months() as $month)
        {
            $sum = 0;
            foreach((new SalesReport())->getRecords(['fromDate' => $month['from'], 'toDate' => $month['to']]) as $record)
            {
                $sum += $record['grossProfit'];
            }
            $profit[] = number_format($sum, 2, '.', '');
        }

        return Chart::series(Chart::AREA, array_column($this->months(), 'name'), [
            $this->t('graphs.total_income_graph.IncomeGraphSetTitle', ['currencySymbol' => Helpers::getCurrencySymbol()]) => $profit,
        ]);
    }

    /** Gross profit per month of the products that earned most in the last six months */
    protected function productIncome(): array
    {
        $names  = [];
        $profit = [];
        $months = $this->months();
        foreach($months as $index => $month)
        {
            foreach((new SalesReport())->getRecords(['fromDate' => $month['from'], 'toDate' => $month['to']]) as $record)
            {
                $names[$record['productId']]          = $record['productName'];
                $profit[$record['productId']][$index] = ($profit[$record['productId']][$index] ?? 0) + $record['grossProfit'];
            }
        }
        $totals = array_map('array_sum', $profit);
        arsort($totals);

        $series = [];
        foreach(array_slice(array_keys($totals), 0, self::TOP_PRODUCTS) as $productId)
        {
            $values = [];
            foreach(array_keys($months) as $index)
            {
                $values[] = number_format((float)($profit[$productId][$index] ?? 0), 2, '.', '');
            }
            $series[$names[$productId]] = $values;
        }

        return Chart::series(Chart::AREA, array_column($months, 'name'), $series);
    }

    /** Active certificates that have expired or expire within 30 / 60 / 90 days */
    protected function expiring(): array
    {
        $today   = date('Y-m-d');
        $buckets = ['expired' => 0, 'days30' => 0, 'days60' => 0, 'days90' => 0];
        foreach($this->services([]) as $record)
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
                $buckets['days30']++;
            }
            elseif($expires <= date('Y-m-d', strtotime('+60 day')))
            {
                $buckets['days60']++;
            }
            elseif($expires <= date('Y-m-d', strtotime('+90 day')))
            {
                $buckets['days90']++;
            }
        }
        $labels = [];
        foreach(array_keys($buckets) as $key)
        {
            $labels[] = $this->t('graphs.expiring_certificates_graph.' . $key);
        }

        return Chart::series(Chart::BAR, $labels, [$this->t('graphs.expiring_certificates_graph.certificates') => array_values($buckets)]);
    }

    protected function ordersByStatus(): array
    {
        $counts = [];
        foreach($this->services([]) as $record)
        {
            $status          = $record['status'] ?: 'Unknown';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        arsort($counts);

        return Chart::parts($counts);
    }

    /** Sales of the last six months by certificate brand */
    protected function revenueByBrand(): array
    {
        $sums = [];
        foreach($this->services(['fromDate' => date('Y-m-01 00:00:00', strtotime('-5 months')), 'toDate' => date('Y-m-t 23:59:59')]) as $record)
        {
            $name        = $record['brand'] ?: 'Other';
            $sums[$name] = ($sums[$name] ?? 0) + $record['salesAmount'];
        }
        arsort($sums);

        return Chart::parts(array_map(function($sum) {
            return round($sum, 2);
        }, $sums));
    }

    /** @return array report records, one per service */
    protected function services(array $filters): array
    {
        $records = [];
        foreach((new SalesReport())->getRecords($filters) as $record)
        {
            $records[$record['serviceId']] = $record;
        }

        return $records;
    }

    /** @return array[] the last six months, oldest first: name, from, to */
    protected function months(): array
    {
        $months = [];
        $middle = date('Y-m-') . '15';
        for($i = 5; $i >= 0; $i--)
        {
            $time     = strtotime($middle . ' - ' . $i . ' months');
            $months[] = ['name' => date('F', $time), 'from' => date('Y-m-', $time) . '01 00:00:00', 'to' => date('Y-m-t 23:59:59', $time)];
        }

        return $months;
    }

    /** The five clients who paid most for this module's products */
    protected function bestClients(): array
    {
        $rows = Capsule::table('tblinvoiceitems')
            ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblproducts.servertype', Kernel::module())
            ->select(Capsule::raw('tblinvoiceitems.userid as clientId, SUM(`firstpaymentamount`) as paid'))
            ->groupBy('tblinvoiceitems.userid')
            ->get();

        $paid = [];
        foreach($rows as $row)
        {
            $paid[(int)$row->clientId] = Helpers::clientCurrencyToDefaultCurrency($row->paid, $row->clientId);
        }
        arsort($paid);

        $clients = [];
        foreach(array_slice($paid, 0, 5, true) as $clientId => $amount)
        {
            $clients[] = ['id' => $clientId, 'name' => self::clientName($clientId), 'paid' => Helpers::formatDefaultCurrency($amount)];
        }

        return $clients;
    }

    /** The five newest invoices with a product of this module */
    protected function latestInvoices(): array
    {
        $rows = Capsule::table('tblinvoices')
            ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->join('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->where('tblproducts.servertype', Kernel::module())
            ->select(['tblinvoices.*'])
            ->groupBy('tblinvoices.id')
            ->orderBy('tblinvoices.id', 'DESC')
            ->limit(5)
            ->get();

        $invoices = [];
        foreach($rows as $row)
        {
            $invoices[] = [
                'id'       => (int)$row->id,
                'clientId' => (int)$row->userid,
                'client'   => self::clientName((int)$row->userid),
                'status'   => (string)$row->status,
                'paid'     => $row->status === 'Paid',
                'amount'   => Helpers::formatDefaultCurrency(Helpers::clientCurrencyToDefaultCurrency($row->total, $row->userid)),
                'date'     => (string)$row->date,
            ];
        }

        return $invoices;
    }

    protected static function clientName(int $clientId): string
    {
        $client = Capsule::table('tblclients')->where('id', $clientId)->first(['firstname', 'lastname', 'companyname']);

        return $client ? ($client->companyname ?: trim($client->firstname . ' ' . $client->lastname)) : '';
    }
}
