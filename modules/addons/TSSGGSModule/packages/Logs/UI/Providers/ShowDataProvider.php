<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;
use WHMCS\Database\Capsule;

/**
 * The "Show details" window: the entry, the service / client it belongs to, the API request and answer when the entry
 * is a gateway call, whatever else was stored, and the neighbouring entries of the same service (the trace around it).
 */
class ShowDataProvider extends CrudProvider
{
    const NEARBY_MINUTES = 10;
    const NEARBY_LIMIT   = 20;

    public function read()
    {
        $log = Logs::where('id', $this->formData['id'])->first();
        if(!$log)
        {
            $this->data->createFrom(['data' => []]);

            return;
        }

        $raw    = (array)$log->data;
        $api    = isset($raw['api']) && is_array($raw['api']) ? $raw['api'] : null;
        $out    = ['Entry' => [
            'id'      => (int)$log->id,
            'type'    => ucfirst((string)$log->type),
            'date'    => (string)$log->date,
            'message' => (string)$log->message,
        ]];
        $serviceId = (int)($raw['service'] ?? 0);

        if($serviceId && ($related = $this->related($serviceId)))
        {
            $out['Related'] = $related;
        }

        if($api)
        {
            $out['Request']  = [
                'provider' => $api['provider'] ?? '',
                'method'   => $api['method'] ?? '',
                'url'      => $api['url'] ?? '',
                'body'     => $api['request'] ?? '',
            ];
            $out['Response'] = [
                'http_code' => $api['http_code'] ?? '',
                'duration'  => $api['duration'] ?? '',
                'body'      => $api['response'] ?? '',
            ];
        }

        $rest = array_diff_key($raw, ['api' => 1, 'service' => 1]);
        if($rest)
        {
            $out['Data'] = $rest;
        }

        if($serviceId && ($nearby = $this->nearby($log, $serviceId)))
        {
            $out['Same service, around this time'] = $nearby;
        }

        $this->data->createFrom(['data' => $out]);
    }

    protected function related(int $serviceId): array
    {
        $row = Capsule::table('tblhosting as h')
            ->leftJoin('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->leftJoin('tblclients as c', 'c.id', '=', 'h.userid')
            ->leftJoin('tblsslorders as o', 'o.serviceid', '=', 'h.id')
            ->where('h.id', $serviceId)
            ->first(['h.domain', 'h.domainstatus', 'h.userid', 'p.name as product', 'c.firstname', 'c.lastname', 'c.companyname', 'o.remoteid', 'o.status as orderstatus']);
        if(!$row)
        {
            return ['service' => '#' . $serviceId . ' (deleted)'];
        }

        return array_filter([
            'service'      => '#' . $serviceId . ($row->domain ? ' ' . $row->domain : ''),
            'product'      => $row->product,
            'client'       => '#' . $row->userid . ' ' . trim($row->firstname . ' ' . $row->lastname) . ($row->companyname ? ' (' . $row->companyname . ')' : ''),
            'service_status' => $row->domainstatus,
            'vendor_order_id' => $row->remoteid,
            'order_status' => $row->orderstatus,
        ], function($value) {
            return $value !== null && $value !== '';
        });
    }

    protected function nearby($log, int $serviceId): array
    {
        $time   = strtotime((string)$log->date) ?: time();
        $window = self::NEARBY_MINUTES * 60;

        return Logs::where('id', '!=', $log->id)
            ->where('data', 'REGEXP', '"service":' . $serviceId . '[,}]')
            ->whereBetween('date', [date('Y-m-d H:i:s', $time - $window), date('Y-m-d H:i:s', $time + $window)])
            ->orderBy('id')
            ->limit(self::NEARBY_LIMIT)
            ->get(['id', 'type', 'date', 'message'])
            ->map(function($entry) {
                return '#' . $entry->id . ' ' . substr((string)$entry->date, 11) . ' [' . ucfirst((string)$entry->type) . '] ' . $entry->message;
            })
            ->all();
    }
}
