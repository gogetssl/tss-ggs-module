<?php

namespace TSSGGSModule\App\Libs\Certificates;

use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * The approver e-mail address of each name validated by e-mail, kept with the service: tblsslorders.configdata
 * "approver_emails" = [name => address]. The order details of the gateway do not always carry the address (some
 * vendors answer only "email"), so the client's order page and the admin service tab show it from here.
 *
 * Written when the order is placed, when a validation method is changed (client, admin, AutoInstall) and on a reissue.
 * Orders placed before the list was kept, and migrated ones, fall back to the addresses sent with the order.
 */
class ApproverEmails
{
    const KEY = 'approver_emails';

    /** A new order or a reissue: [name => method or address] replaces the whole list */
    public static function replace($serviceId, array $methods)
    {
        self::write($serviceId, self::addressesOf($methods));
    }

    /** A change of some names: an address is kept, any other method drops the name */
    public static function change($serviceId, array $methods)
    {
        $list = self::stored($serviceId);
        $list = $list === null ? self::fromOrderData($serviceId) : $list;
        foreach($methods as $name => $method)
        {
            $name = strtolower(trim((string)$name));
            if(self::isAddress($method))
            {
                $list[$name] = trim((string)$method);
            }
            else
            {
                unset($list[$name]);
            }
        }
        self::write($serviceId, $list);
    }

    /** [name (lower case) => address] of the service */
    public static function forService($serviceId): array
    {
        try
        {
            $list = self::stored($serviceId);

            return $list === null ? self::fromOrderData($serviceId) : $list;
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }

    /**
     * The address of a name for display: the method itself when the gateway reports the address, else the stored one;
     * '' when the name is not validated by e-mail or no address is known
     */
    public static function addressFor(array $list, $name, $method): string
    {
        if(self::isAddress($method))
        {
            return trim((string)$method);
        }
        if(strtolower(trim((string)$method)) !== 'email')
        {
            return '';
        }

        return (string)($list[strtolower(trim((string)$name))] ?? '');
    }

    public static function isAddress($method): bool
    {
        return is_string($method) && strpos($method, '@') !== false;
    }

    /** null when nothing was stored yet (older and migrated orders) */
    private static function stored($serviceId)
    {
        $raw  = Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->value('configdata');
        $data = json_decode((string)$raw, true);
        if(!is_array($data) || !isset($data[self::KEY]) || !is_array($data[self::KEY]))
        {
            return null;
        }

        return $data[self::KEY];
    }

    /** Keeping the list must never fail the order or the change it belongs to */
    private static function write($serviceId, array $list)
    {
        try
        {
            $raw  = Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->value('configdata');
            $data = json_decode((string)$raw, true);
            // data in another format (a legacy module's) is never overwritten: the list then falls back to the order data
            if(!is_array($data) && trim((string)$raw) !== '')
            {
                return;
            }
            $data = is_array($data) ? $data : [];
            $data[self::KEY] = $list;
            Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['configdata' => json_encode($data)]);
        }
        catch(\Throwable $e)
        {
        }
    }

    /** [name => method or address] => [name (lower case) => address] */
    private static function addressesOf(array $methods): array
    {
        $list = [];
        foreach($methods as $name => $method)
        {
            if(self::isAddress($method))
            {
                $list[strtolower(trim((string)$name))] = trim((string)$method);
            }
        }

        return $list;
    }

    /**
     * The addresses sent with the order: the stored order data (it follows client changes) or the request of the current
     * order (after a renewal the renewal's); migrated orders carry the legacy address as the method there too
     */
    private static function fromOrderData($serviceId): array
    {
        $sources = [];
        $certificate = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->value('request');
        if($certificate)
        {
            $decoded   = json_decode((string)\decrypt($certificate), true);
            $sources[] = is_array($decoded) ? ($decoded['orderData']['orderData'] ?? []) : [];
        }
        $request = Request::currentOrder((int)$serviceId);
        if($request && !empty($request->request))
        {
            $decoded   = json_decode((string)\decrypt($request->request), true);
            $sources[] = is_array($decoded) ? $decoded : [];
        }

        foreach($sources as $order)
        {
            if(!is_array($order) || empty($order['common_name']['name']))
            {
                continue;
            }
            $methods = [(string)$order['common_name']['name'] => $order['common_name']['validation_method'] ?? ''];
            foreach((array)($order['alternative_names'] ?? []) as $san)
            {
                if(!empty($san['name']))
                {
                    $methods[(string)$san['name']] = $san['validation_method'] ?? '';
                }
            }

            return self::addressesOf($methods);
        }

        return [];
    }
}
