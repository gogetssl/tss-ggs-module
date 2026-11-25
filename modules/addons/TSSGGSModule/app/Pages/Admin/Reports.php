<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Layout;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Kernel\Table;
use TSSGGSModule\App\Libs\Billing\SalesReport;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\Helpers;

/**
 * Admin page "Reports": three reports on the invoiced services of this module's products (App\Libs\Billing\
 * SalesReport) - renewals, sales by date, profit and loss - each with its filters, a searchable list and a CSV
 * export. A report is loaded when its tab is opened (it reads all the records).
 * Templates: resources/templates/admin/reports.
 */
class Reports extends Page
{
    const LANG = 'admin.reports.index';

    /** report => texts group, columns of the list and of the CSV file, filters, only paid invoices */
    const REPORTS = [
        'renewal' => [
            'title'   => 'reports_container.renewalReport',
            'texts'   => 'data_tables.renewal',
            'columns' => ['date', 'storeId', 'clientDetails', 'productDetails', 'type', 'brand', 'status', 'issueDate', 'expirationDate'],
            'filters' => ['renewalPeriod'],
            'paid'    => false,
        ],
        'sales'   => [
            'title'   => 'reports_container.datewiseSalesReport',
            'texts'   => 'data_tables.datewise_sales',
            'columns' => ['date', 'storeId', 'clientDetails', 'productDetails', 'type', 'brand', 'status', 'salesAmount', 'cost', 'payment', 'paymentStatus'],
            'filters' => ['fromDate', 'toDate', 'brand', 'productId', 'sslStatus', 'vendorOrderId'],
            'paid'    => false,
        ],
        'profit'  => [
            'title'   => 'reports_container.profitLossReport',
            'texts'   => 'data_tables.profit_loss',
            'columns' => ['date', 'storeId', 'clientDetails', 'productDetails', 'type', 'brand', 'salesAmount', 'cost', 'grossProfit', 'payment', 'paymentStatus'],
            'filters' => ['fromDate', 'toDate'],
            'paid'    => true,
        ],
    ];

    const RENEWAL_PERIODS = ['all_all' => 'all', 'last_30' => 'last30', 'last_60' => 'last60', 'last_90' => 'last90', 'last_all' => 'lastAll',
                             'next_30' => 'next30', 'next_60' => 'next60', 'next_90' => 'next90', 'next_all' => 'nextAll'];

    const SSL_STATUSES = ['active', 'pending', 'expired', 'cancelled'];

    /** colours of the order statuses: green = done, yellow = waiting for someone, blue = in progress, red = ended or failed */
    const ORDER_TONES = [
        'success' => ['active', 'issued'],
        'warning' => ['pending', 'incomplete', 'need_action', 'awaiting validation', 'unpaid', 'suspended'],
        'info'    => ['processing', 'new_order', 'user_confirmed', 'pre_issue', 'reissue', 'renew'],
        'danger'  => ['cancelled', 'canceled', 'terminated', 'expired', 'rejected', 'revoked', 'refunded', 'failed', 'fraud'],
    ];

    /** colours of the invoice statuses; any other (overdue, cancelled, refunded, collections) is red */
    const PAYMENT_TONES = ['success' => ['paid'], 'warning' => ['pending', 'unpaid', 'payment pending'], 'default' => ['draft']];

    public function index()
    {
        $reports = [];
        foreach(self::REPORTS as $name => $report)
        {
            $reports[] = ['name' => $name, 'title' => $this->t($report['title'])];
        }

        return $this->view('admin/reports/index', [
            'showInfo' => !GeneralSettings::enabled('hideInfoMessages'),
            'reports'  => $reports,
            'tab'      => $this->request->oneOf('tab', array_keys(self::REPORTS), 'renewal'),
        ]);
    }

    /** GET name + the report's filters: the report (filters, summary, list); the list alone when it reloads itself */
    public function report()
    {
        $name = $this->request->oneOf('name', array_keys(self::REPORTS));
        if($name === '')
        {
            return Response::fragment(Layout::notFound($this), 404);
        }
        $report  = self::REPORTS[$name];
        $filters = $this->filters($name);
        $records = (new SalesReport())->getRecords($filters, $report['paid']);

        $table = Table::make($this->request, $name)->sortBy('date', 'desc')->perPage([10, 25])
            ->rowTemplate('admin/reports/row')->toolbar('admin/reports/toolbar')
            ->keep(['mod-action' => 'report', 'name' => $name] + $filters);
        foreach($report['columns'] as $column)
        {
            $table->column($column, $this->t($report['texts'] . '.array_data_provider.data_table.' . $column), true, Table::SEARCH_TEXT);
        }
        $table = $table->rows(array_map([$this, 'cells'], $records));
        $table['csvUrl']  = $this->url('csv', ['name' => $name] + $filters);
        $table['csvText'] = $this->t($report['texts'] . '.forms.download_form.downloadCsv');

        if($this->request->string('table') === $name)
        {
            return $this->fragment('admin/partials/table', ['table' => $table]);
        }

        return $this->fragment('admin/reports/report', [
            'name'     => $name,
            'texts'    => $report['texts'],
            'filters'  => $filters,
            'has'      => array_fill_keys($report['filters'], true),
            'summary'  => $this->summary($name, $filters),
            'table'    => $table,
            'periods'  => self::RENEWAL_PERIODS,
            'statuses' => self::SSL_STATUSES,
            'brands'   => $name === 'sales' ? array_values(array_filter(Helpers::getBrandOptions())) : [],
            'products' => $name === 'sales' ? array_diff_key(Helpers::getProductOptions(), [0 => true]) : [],
            'advanced' => (bool)array_filter(array_intersect_key($filters, array_flip(['brand', 'productId', 'sslStatus', 'vendorOrderId']))),
        ]);
    }

    /** GET name + the report's filters: the report as a CSV file */
    public function csv()
    {
        $name = $this->request->oneOf('name', array_keys(self::REPORTS));
        if($name === '')
        {
            return $this->redirect();
        }
        $report = self::REPORTS[$name];

        return Response::download((new SalesReport())->csv($this->filters($name), $report['columns'], $report['paid']), 'report.csv');
    }

    /** @return array the report's filters as the request gives them */
    protected function filters(string $name): array
    {
        $filters = [];
        foreach(self::REPORTS[$name]['filters'] as $filter)
        {
            $value = $this->request->string($filter);
            if(in_array($filter, ['fromDate', 'toDate'], true) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
            {
                $value = '';
            }
            if($filter === 'renewalPeriod')
            {
                $value = isset(self::RENEWAL_PERIODS[$value]) ? $value : 'all_all';
            }
            if($filter === 'sslStatus' && !in_array($value, self::SSL_STATUSES, true))
            {
                $value = '';
            }
            if($filter === 'productId')
            {
                $value = (string)(max(0, (int)$value) ?: '');
            }
            $filters[$filter] = $value;
        }

        return $filters;
    }

    /** The line above a report's list (ready HTML is avoided: label, value, colour) */
    protected function summary(string $name, array $filters): array
    {
        if($name === 'renewal')
        {
            return [];
        }
        $records = (new SalesReport())->getRecords(['fromDate' => $filters['fromDate'], 'toDate' => $filters['toDate']]);
        $texts   = self::REPORTS[$name]['texts'] . '.widgets.header.';

        if($name === 'profit')
        {
            $profit = array_sum(array_column($records, 'grossProfit'));

            return [
                [['label' => $this->t($texts . 'totalGrossProfit'), 'value' => Helpers::formatDefaultCurrency($profit), 'color' => $profit > 0 ? 'green' : 'red']],
                [['label' => $this->t($texts . 'certificateCount'), 'value' => (string)count($records), 'color' => 'green']],
            ];
        }

        $sums = ['Paid' => 0, 'Unpaid' => 0, 'Overdue' => 0];
        foreach($records as $record)
        {
            if(isset($sums[$record['paymentStatus']]))
            {
                $sums[$record['paymentStatus']] += $record['salesAmount'];
            }
        }

        return [[
            ['label' => $this->t($texts . 'paid'), 'value' => Helpers::formatDefaultCurrency($sums['Paid']), 'color' => 'green'],
            ['label' => $this->t($texts . 'unpaid'), 'value' => Helpers::formatDefaultCurrency($sums['Unpaid']), 'color' => 'red'],
            ['label' => $this->t($texts . 'overdue'), 'value' => Helpers::formatDefaultCurrency($sums['Overdue']), 'color' => 'yellow'],
        ]];
    }

    /** A record as the list shows it: money formatted, the colours of the two statuses added */
    protected function cells(array $record): array
    {
        $hasCost = (bool)$record['cost'];

        return [
            'salesAmountText' => Helpers::formatDefaultCurrency($record['salesAmount']),
            'costText'        => $hasCost ? Helpers::formatDefaultCurrency($record['cost']) : '-',
            'grossProfitText' => $hasCost ? Helpers::formatDefaultCurrency($record['grossProfit']) : '-',
            'statusTone'      => self::tone(self::ORDER_TONES, $record['status'], 'default'),
            'paymentTone'     => self::tone(self::PAYMENT_TONES, $record['paymentStatus'], 'danger'),
        ] + $record;
    }

    protected static function tone(array $tones, $status, string $default): string
    {
        $status = strtolower(trim((string)$status));
        foreach($tones as $tone => $statuses)
        {
            if(in_array($status, $statuses, true))
            {
                return $tone;
            }
        }

        return $default;
    }
}
