<?php

namespace TSSGGSModule\App\Libs\Logs;

use TSSGGSModule\App\Libs\Settings\GeneralSettingsProvider;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\LogPolicy;
use TSSGGSModule\App\Models\Log;
use TSSGGSModule\App\Libs\Support\ModuleSettings;
use WHMCS\Database\Capsule;

/**
 * The module log as the Logs page works with it: the list with its search, the details of one entry, deleting,
 * the CSV export and what is recorded (settings). Writing entries is Packages\Logs\Services\Logs.
 */
class LogBook
{
    const MAX_SERVICES   = 200;
    const NEARBY_MINUTES = 10;
    const NEARBY_LIMIT   = 20;

    /** every type an entry can have (PSR-3 levels) */
    const LEVELS = ['alert', 'critical', 'debug', 'emergency', 'error', 'info', 'notice', 'warning'];

    /** type => colour of its label and of its card's button, icon of its card */
    const TYPES = [
        'emergency' => ['tone' => 'danger', 'icon' => 'alert-outline'],
        'alert'     => ['tone' => 'primary', 'icon' => 'alert-circle-outline'],
        'critical'  => ['tone' => 'danger', 'icon' => 'alert-octagon'],
        'error'     => ['tone' => 'danger', 'icon' => 'alert-octagon'],
        'warning'   => ['tone' => 'warning', 'icon' => 'alert-circle'],
        'notice'    => ['tone' => 'info', 'icon' => 'information-outline'],
        'info'      => ['tone' => 'success', 'icon' => 'information-outline'],
        'debug'     => ['tone' => 'default', 'icon' => 'bug'],
    ];

    /* ---- the list ---- */

    /** @return \Illuminate\Database\Query\Builder the entries, optionally of one type */
    public static function query(string $type = '')
    {
        $query = Log::query()->toBase()->select('id', 'type', 'date', 'data', 'message');
        if($type !== '')
        {
            $query->where('type', $type);
        }

        return $query;
    }

    /**
     * The search of the list: the ID, message, type and date as usual, plus the entries of a service - "#71" (the
     * service ID shown in Related Item), a domain, a product or client name, or the vendor order ID. The service is
     * kept inside the entry data, so those entries are found through it.
     *
     * @param callable $usual adds the usual column search to a (sub)query (App\Kernel\Table::searchUsing)
     */
    public static function search($query, string $term, callable $usual): void
    {
        $term        = trim($term);
        $serviceOnly = strpos($term, '#') === 0;
        $plain       = trim(ltrim($term, '#'));
        $serviceIds  = $plain === '' ? [] : self::serviceIds($plain);

        $query->where(function($q) use ($serviceOnly, $serviceIds, $usual) {
            if(!$serviceOnly)
            {
                $usual($q);
            }
            if($serviceIds)
            {
                $q->orWhereRaw('data REGEXP ?', ['"service":(' . implode('|', array_map('intval', $serviceIds)) . ')[,}]']);
            }
            elseif($serviceOnly)
            {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /** IDs of the services a search term points at */
    protected static function serviceIds(string $term): array
    {
        $like  = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        $query = Capsule::table('tblhosting as h')
            ->leftJoin('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->leftJoin('tblclients as c', 'c.id', '=', 'h.userid')
            ->leftJoin('tblsslorders as o', 'o.serviceid', '=', 'h.id')
            ->where(function($q) use ($term, $like) {
                if(ctype_digit($term))
                {
                    $q->where('h.id', (int)$term)->orWhere('o.remoteid', $term);
                }
                $q->orWhere('h.domain', 'like', $like)
                    ->orWhere('p.name', 'like', $like)
                    ->orWhere('c.companyname', 'like', $like)
                    ->orWhereRaw("CONCAT(c.firstname, ' ', c.lastname) LIKE ?", [$like]);
            });

        return $query->limit(self::MAX_SERVICES)->pluck('h.id')->map(function($id) {
            return (int)$id;
        })->all();
    }

    /**
     * A row of the list as the page shows it: the message with its values filled in and how often it repeated,
     * what the entry is about (text and link), the colour of its type, whether it has details.
     *
     * @param array $row id, type, date, data (JSON text), message
     */
    public static function present(array $row): array
    {
        $data    = json_decode((string)($row['data'] ?? ''), true);
        $data    = is_array($data) ? $data : [];
        $replace = [];
        foreach($data as $key => $value)
        {
            if(is_scalar($value))
            {
                $replace[':' . $key] = (string)$value;
            }
        }
        $repeats = !empty($data['repeats']) ? ' (x' . ((int)$data['repeats'] + 1) . ', since ' . substr((string)($data['since'] ?? ''), 11, 5) . ')' : '';

        return [
            'id'         => (int)$row['id'],
            'type'       => (string)$row['type'],
            'label'      => strtoupper((string)$row['type']),
            'tone'       => self::TYPES[$row['type']]['tone'] ?? 'default',
            'date'       => (string)$row['date'],
            'message'    => strtr((string)$row['message'], $replace) . $repeats,
            'related'    => self::relatedItem($data),
            'hasDetails' => (bool)$data,
        ];
    }

    /**
     * What an entry is about: the first of client, service, addon, domain, invoice, ticket, order its data names
     * and that still exists.
     *
     * @return array|null text, url
     */
    public static function relatedItem(array $data): ?array
    {
        $id = function($name) use ($data) {
            return isset($data[$name]) && is_scalar($data[$name]) ? (int)$data[$name] : 0;
        };

        if($id('client') && ($client = Capsule::table('tblclients')->where('id', $id('client'))->first(['id', 'firstname', 'lastname'])))
        {
            return ['text' => '#' . $client->id . ' ' . $client->firstname . ' ' . $client->lastname, 'url' => 'clientssummary.php?userid=' . (int)$client->id];
        }
        if($id('service'))
        {
            $service = Capsule::table('tblhosting as h')->leftJoin('tblproducts as p', 'p.id', '=', 'h.packageid')->where('h.id', $id('service'))->first(['h.id', 'h.userid', 'h.domain', 'p.name']);
            if($service)
            {
                return ['text' => '#' . $service->id . ' ' . $service->name . ((string)$service->domain !== '' ? ' (' . $service->domain . ')' : ''),
                        'url'  => 'clientsservices.php?userid=' . (int)$service->userid . '&productselect=' . (int)$service->id];
            }
        }
        if($id('addon'))
        {
            $addon = Capsule::table('tblhostingaddons as a')->leftJoin('tbladdons as d', 'd.id', '=', 'a.addonid')->where('a.id', $id('addon'))->first(['a.id', 'a.userid', 'd.name']);
            if($addon)
            {
                return ['text' => '#' . $addon->id . ' ' . $addon->name, 'url' => 'clientsservices.php?userid=' . (int)$addon->userid . '&productselect=a' . (int)$addon->id];
            }
        }
        if($id('domain') && ($domain = Capsule::table('tbldomains')->where('id', $id('domain'))->first(['id', 'userid', 'domain'])))
        {
            return ['text' => '#' . $domain->id . ' ' . $domain->domain, 'url' => 'clientsdomains.php?userid=' . (int)$domain->userid . '&id=' . (int)$domain->id];
        }
        if($id('invoice') && ($invoice = Capsule::table('tblinvoices')->where('id', $id('invoice'))->first(['id', 'invoicenum'])))
        {
            return ['text' => '#' . (trim((string)$invoice->invoicenum) !== '' ? $invoice->invoicenum : $invoice->id), 'kind' => 'invoice', 'url' => 'invoices.php?action=edit&id=' . (int)$invoice->id];
        }
        if($id('ticket') && Capsule::table('tbltickets')->where('id', $id('ticket'))->exists())
        {
            return ['text' => '#' . $id('ticket'), 'kind' => 'ticket', 'url' => 'supporttickets.php?action=view&id=' . $id('ticket')];
        }
        if($id('order') && Capsule::table('tblorders')->where('id', $id('order'))->exists())
        {
            return ['text' => '#' . $id('order'), 'kind' => 'order', 'url' => 'orders.php?action=view&id=' . $id('order')];
        }

        return null;
    }

    /** @return array type => number of entries, the types that have entries, then "total" */
    public static function counts(): array
    {
        $found = [];
        foreach(Log::query()->toBase()->selectRaw('type, count(id) as entries')->groupBy('type')->get() as $row)
        {
            $found[(string)$row->type] = (int)$row->entries;
        }
        // from the everyday entries to the gravest ones
        $counts = [];
        foreach(['info', 'notice', 'debug', 'warning', 'error', 'critical', 'alert', 'emergency'] as $type)
        {
            if(isset($found[$type]))
            {
                $counts[$type] = $found[$type];
            }
        }
        $counts += $found;

        return $counts + ['total' => array_sum($counts)];
    }

    /** @return string[] the types that have entries */
    public static function usedTypes(): array
    {
        return array_map('strval', Log::query()->toBase()->distinct()->orderBy('type')->pluck('type')->all());
    }

    /* ---- one entry ---- */

    /**
     * "Show details": the entry, the service / client it belongs to, the API request and answer when the entry is a
     * gateway call, whatever else was stored, and the neighbouring entries of the same service (the trace around it).
     *
     * @return array|null null when the entry is gone
     */
    public static function details(int $id): ?array
    {
        $log = Log::where('id', $id)->first();
        if(!$log)
        {
            return null;
        }

        $raw = (array)$log->data;
        $api = isset($raw['api']) && is_array($raw['api']) ? $raw['api'] : null;
        $out = ['Entry' => [
            'id'      => (int)$log->id,
            'type'    => ucfirst((string)$log->type),
            'date'    => (string)$log->date,
            'message' => (string)$log->message,
        ]];
        $serviceId = (int)($raw['service'] ?? 0);

        if($serviceId && ($related = self::relatedService($serviceId)))
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
        if($serviceId && ($nearby = self::nearby($log, $serviceId)))
        {
            $out['Same service, around this time'] = $nearby;
        }

        return $out;
    }

    protected static function relatedService(int $serviceId): array
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
            'service'         => '#' . $serviceId . ($row->domain ? ' ' . $row->domain : ''),
            'product'         => $row->product,
            'client'          => '#' . $row->userid . ' ' . trim($row->firstname . ' ' . $row->lastname) . ($row->companyname ? ' (' . $row->companyname . ')' : ''),
            'service_status'  => $row->domainstatus,
            'vendor_order_id' => $row->remoteid,
            'order_status'    => $row->orderstatus,
        ], function($value) {
            return $value !== null && $value !== '';
        });
    }

    protected static function nearby($log, int $serviceId): array
    {
        $time   = strtotime((string)$log->date) ?: time();
        $window = self::NEARBY_MINUTES * 60;

        return Log::where('id', '!=', $log->id)
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

    /**
     * The details as coloured JSON (HTML for a <pre>): keys, texts, numbers, true / false / null each in their own
     * colour. The values that tell what went wrong are marked: an error type, the message of an error entry and an
     * exception class.
     */
    public static function highlight(array $details): string
    {
        $json    = htmlspecialchars((string)json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR), ENT_NOQUOTES);
        $isError = (bool)preg_match('/^(error|critical|alert|emergency)$/i', (string)($details['Entry']['type'] ?? ''));
        $key     = '';

        return (string)preg_replace_callback('/("(\\\\u[a-zA-Z0-9]{4}|\\\\[^u]|[^\\\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/', function($m) use (&$key, $isError) {
            $match = $m[0];
            $class = 'number';
            if($match[0] === '"')
            {
                if(preg_match('/:$/', $match))
                {
                    // the key in its own colour, the colon stays punctuation
                    $key = (string)preg_replace('/\s*:$/', '', $match);

                    return '<span class="key">' . $key . '</span><span class="punct">:</span>';
                }
                $class = 'string';
                if(($key === '"type"' && preg_match('/^"(error|critical|alert|emergency)"$/i', $match))
                    || ($key === '"message"' && $isError)
                    || ($key === '"class"' && preg_match('/(exception|error)"$/i', $match)))
                {
                    $class .= ' alert';
                }
            }
            elseif($match === 'true' || $match === 'false')
            {
                $class = 'boolean';
            }
            elseif($match === 'null')
            {
                $class = 'null';
            }
            $key = '';

            return '<span class="' . $class . '">' . $match . '</span>';
        }, $json);
    }

    /* ---- deleting ---- */

    /** @param int[] $ids */
    public static function delete(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        return $ids ? (int)Log::whereIn('id', $ids)->delete() : 0;
    }

    /** Every entry of the module log */
    public static function clean(): void
    {
        Log::query()->delete();
    }

    /**
     * Entries of the given types (none given: of every type) older than so many days (0: of any age).
     *
     * @param string[] $types
     */
    public static function prune(array $types, int $olderThanDays): int
    {
        $query = Log::query();
        $types = array_values(array_intersect($types, self::LEVELS));
        if($types)
        {
            $query->whereIn('type', $types);
        }
        if($olderThanDays > 0)
        {
            $query->where('date', '<', date('Y-m-d', time() - $olderThanDays * 86400));
        }

        return (int)$query->delete();
    }

    /* ---- export ---- */

    /** @return array from, to: the dates of the oldest and the newest entry (Y-m-d; today when the log is empty) */
    public static function period(): array
    {
        $from = (string)Log::query()->toBase()->min('date');
        $to   = (string)Log::query()->toBase()->max('date');

        return ['from' => substr($from, 0, 10) ?: date('Y-m-d'), 'to' => substr($to, 0, 10) ?: date('Y-m-d')];
    }

    /**
     * The entries of a period and of the given types (none given: every type) as CSV.
     *
     * @return string|null null when no entry matches
     */
    public static function csv(string $from, string $to, array $types): ?string
    {
        $query = Log::query()->toBase()->select('id', 'type', 'date', 'message', 'data')->orderBy('id');
        if($from !== '' && ($time = strtotime($from)))
        {
            $query->where('date', '>=', date('Y-m-d', $time));
        }
        if($to !== '' && ($time = strtotime($to)))
        {
            $query->where('date', '<', date('Y-m-d', $time + 86400));
        }
        $types = array_values(array_intersect($types, self::LEVELS));
        if($types)
        {
            $query->whereIn('type', $types);
        }

        $out   = fopen('php://temp', 'r+');
        $count = 0;
        fputcsv($out, ['id', 'type', 'date', 'message', 'data']);
        $query->chunk(500, function($rows) use ($out, &$count) {
            foreach($rows as $row)
            {
                fputcsv($out, \TSSGGSModule\App\Libs\Support\Csv::row([(int)$row->id, (string)$row->type, (string)$row->date, (string)$row->message, (string)$row->data]));
                $count++;
            }
        });
        rewind($out);
        $csv = (string)stream_get_contents($out);
        fclose($out);

        return $count ? $csv : null;
    }

    /* ---- what is recorded ---- */

    /** @return array the values of the Settings dialog */
    public static function settings(): array
    {
        $days = ModuleSettings::get('logs.auto_prune_older_than', 30);

        return [
            'logging'               => GeneralSettings::enabled('internalLog'),
            'api_mode'              => LogPolicy::apiMode(),
            'events'                => GeneralSettings::enabled('eventLog'),
            'debug_services'        => implode(', ', LogPolicy::debugServices()),
            'debug_hours'           => '4',
            'types'                 => array_values(array_filter((array)ModuleSettings::get('logs.types', []))),
            'auto_prune'            => (bool)ModuleSettings::get('logs.auto_prune', 1),
            'auto_prune_older_than' => is_numeric($days) ? (int)$days : 30,
        ];
    }

    /**
     * Saves the Settings dialog.
     *
     * @throws \InvalidArgumentException "auto_prune_older_than" when Clear Automatically is on without a number of days
     */
    public static function saveSettings(array $posted): void
    {
        $on        = function($value) {
            return !empty($value) && !in_array($value, ['off', 'false', '0'], true);
        };
        $autoPrune = $on($posted['auto_prune'] ?? '');
        $days      = (int)($posted['auto_prune_older_than'] ?? 0);
        if($autoPrune && $days < 1)
        {
            throw new \InvalidArgumentException('auto_prune_older_than');
        }

        GeneralSettingsProvider::saveGeneral([
            'internalLog'   => $posted['logging'] ?? '',
            'apiLogMode'    => in_array($posted['api_mode'] ?? '', LogPolicy::API_MODES, true) ? $posted['api_mode'] : LogPolicy::apiMode(),
            'eventLog'      => $posted['events'] ?? '',
            'debugServices' => (string)($posted['debug_services'] ?? ''),
            'debugHours'    => (int)($posted['debug_hours'] ?? 0),
        ]);

        $save = [
            'logs.types'      => array_values(array_intersect(array_filter((array)($posted['types'] ?? [])), self::LEVELS)) ?: '',
            'logs.auto_prune' => $autoPrune ? 1 : 0,
        ];
        if($autoPrune)
        {
            $save['logs.auto_prune_older_than'] = $days;
        }
        ModuleSettings::save($save);
    }

    /** Ends debug mode before its time runs out */
    public static function stopDebug(): void
    {
        GeneralSettings::save(['debugServices' => '', 'debugUntil' => 0]);
    }
}
