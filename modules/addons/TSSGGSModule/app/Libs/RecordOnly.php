<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * A certificate migrated from the legacy module whose product has no gateway equivalent (discontinued) and that the
 * administrator chose to keep as a record: its order details are kept for reference (Requests row "legacyRecord"),
 * but it can not be renewed, reissued, configured or ordered again, and it is never invoiced for a renewal.
 * The module's crons leave it alone; WHMCS status changes (suspend, unsuspend, terminate) still apply.
 */
class RecordOnly
{
    const REQUEST = 'legacyRecord';

    /** module functions that only change the WHMCS status of the service: allowed */
    const STATUS_ONLY = ['SuspendAccount', 'UnsuspendAccount', 'TerminateAccount'];

    protected static $cache = [];

    public static function isService(int $serviceId): bool
    {
        if($serviceId <= 0)
        {
            return false;
        }
        if(!array_key_exists($serviceId, self::$cache))
        {
            try
            {
                self::$cache[$serviceId] = (bool)Capsule::table(Edition::table('Requests'))->where('serviceid', $serviceId)->where('name', self::REQUEST)->exists();
            }
            catch(\Throwable $e)
            {
                self::$cache[$serviceId] = false;
            }
        }

        return self::$cache[$serviceId];
    }

    /** The stored order details (what the legacy module had) */
    public static function details(int $serviceId): array
    {
        $raw = Capsule::table(Edition::table('Requests'))->where('serviceid', $serviceId)->where('name', self::REQUEST)->orderBy('id', 'desc')->value('request');
        // stored like every request row: encrypted JSON
        $json = $raw && function_exists('decrypt') ? (string)\decrypt((string)$raw) : (string)$raw;
        $data = $json !== '' ? json_decode($json, true) : [];

        return is_array($data) ? $data : [];
    }

    /** Leaves record-only services out of a query on tblhosting (crons, invoicing) */
    public static function exclude($query, string $serviceIdColumn = 'tblhosting.id')
    {
        return $query->whereNotExists(function($q) use ($serviceIdColumn) {
            $q->select(Capsule::raw(1))->from(Edition::table('Requests') . ' as tssggs_rec')
                ->whereColumn('tssggs_rec.serviceid', $serviceIdColumn)->where('tssggs_rec.name', self::REQUEST);
        });
    }

    /** Only record-only services */
    public static function only($query, string $serviceIdColumn = 'tblhosting.id')
    {
        return $query->whereExists(function($q) use ($serviceIdColumn) {
            $q->select(Capsule::raw(1))->from(Edition::table('Requests') . ' as tssggs_rec')
                ->whereColumn('tssggs_rec.serviceid', $serviceIdColumn)->where('tssggs_rec.name', self::REQUEST);
        });
    }

    /**
     * Answer of a server module function for a record-only service, null when the service is not one.
     * $function: the name after "TSSGGSModule_".
     */
    public static function guard(string $function, $params)
    {
        $serviceId = is_array($params) ? (int)($params['serviceid'] ?? 0) : 0;
        if(!self::isService($serviceId))
        {
            return null;
        }
        if(in_array($function, self::STATUS_ONLY, true))
        {
            return 'success';
        }
        switch($function)
        {
            case 'ClientArea':
                return self::view($serviceId, false);
            case 'AdminServicesTabFields':
                return [self::text('tab') => self::view($serviceId, true)];
            case 'AdminServicesTabFieldsSave':
                return true;
            case 'ClientAreaCustomButtonArray':
            case 'AdminCustomButtonArray':
                return [];
            case 'SSLStepOne':
            case 'SSLStepTwo':
            case 'SSLStepThree':
                return ['error' => self::text('refused')];
            default:
                return self::text('refused');
        }
    }

    /** Read-only details with the notice */
    public static function view(int $serviceId, bool $admin): string
    {
        $d     = self::details($serviceId);
        $rows  = [
            self::text('order_id')     => (string)($d['remote_id'] ?? ''),
            self::text('product')      => (string)($d['legacy_product'] ?? ''),
            self::text('common_name')  => (string)($d['common_name'] ?? ''),
            self::text('status')       => (string)($d['legacy_status'] ?? ''),
            self::text('valid_until')  => (string)($d['valid_until'] ?? ''),
            self::text('migrated_at')  => (string)($d['migrated_at'] ?? ''),
        ];
        $html = '<div class="alert alert-info" style="margin-bottom:10px">' . htmlspecialchars(self::text($admin ? 'notice_admin' : 'notice_client')) . '</div>'
            . '<table class="table table-condensed" style="max-width:640px">';
        foreach($rows as $label => $value)
        {
            if($value !== '')
            {
                $html .= '<tr><th style="width:40%">' . htmlspecialchars($label) . '</th><td>' . htmlspecialchars($value) . '</td></tr>';
            }
        }

        return $html . '</table>';
    }

    /** Server module language (client and admin), English when it is not loaded */
    public static function text(string $key): string
    {
        $text = '';
        try
        {
            if(class_exists('\ModulesGarden\Servers\TSSGGSModule\core\Lang'))
            {
                $text = html_entity_decode((string)\ModulesGarden\Servers\TSSGGSModule\core\Lang::absoluteT('recordOnly', $key), ENT_QUOTES, 'UTF-8');
            }
        }
        catch(\Throwable $e)
        {
            $text = '';
        }
        if($text === '' || $text === $key || strpos($text, 'recordOnly') !== false || strpos($text, '_LANG[') !== false)
        {
            $text = self::ENGLISH[$key] ?? $key;
        }

        return $text;
    }

    const ENGLISH = [
        'notice_client' => 'This certificate was moved from our previous system and is kept for your records. Its product is no longer available, so it cannot be renewed, reissued or changed. To continue, please order a new certificate.',
        'notice_admin'  => 'Record only: migrated from the legacy module, its product has no equivalent at the certificate provider. It is never invoiced for a renewal; module actions are not available. Suspend, unsuspend and terminate change the WHMCS status only.',
        'refused'       => 'This certificate is a record from the previous module: its product is no longer available, so this action is not possible. Order a new certificate instead.',
        'tab'           => 'Certificate (record)',
        'order_id'      => 'Order ID',
        'product'       => 'Product',
        'common_name'   => 'Domain',
        'status'        => 'Status at migration',
        'valid_until'   => 'Valid until',
        'migrated_at'   => 'Migrated on',
    ];
}
