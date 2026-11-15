<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * Certificates sold as WHMCS product addons (M-14), the running side.
 *
 * WHMCS calls the module for a product addon with the PARENT service in serviceid / packageid and the addon in
 * addonId. Every part of the module (order, configuration, validation, sync, renewal, AutoInstall, client area) works
 * on a service, so the addon gets one: its "certificate service" - a service of the addon's certificate product, for
 * the same client, billed on the addon (Free Account, amount 0). The module's own actions on the addon are passed on
 * to it: create, suspend, unsuspend, terminate, renew. The addon's due date follows the certificate, and its client
 * and admin pages point to the certificate service's page.
 *
 * Link: Requests row name LINK, serviceid = certificate service, request = {"addon": <tblhostingaddons.id>}.
 */
class AddonCertificates
{
    const LINK = 'addonCertificate';

    /** Statuses of a certificate service that can still be used for its addon */
    const OPEN = ['Pending', 'Active', 'Suspended'];

    // ---- links ---- //

    /** The certificate service of a hosting addon (tblhostingaddons.id), the newest one; null when there is none */
    public static function serviceOf(int $hostingAddonId): ?int
    {
        foreach(Request::where('name', self::LINK)->orderBy('id', 'desc')->get(['serviceid', 'request']) as $row)
        {
            if((int)((json_decode((string)$row->request, true) ?: [])['addon'] ?? 0) === $hostingAddonId
                && Capsule::table('tblhosting')->where('id', (int)$row->serviceid)->exists())
            {
                return (int)$row->serviceid;
            }
        }

        return null;
    }

    /** The hosting addon a certificate service belongs to, null for an ordinary service */
    public static function addonOf(int $serviceId): ?int
    {
        $value = Request::where('serviceid', $serviceId)->where('name', self::LINK)->orderBy('id', 'desc')->value('request');
        $id    = (int)((json_decode((string)$value, true) ?: [])['addon'] ?? 0);

        return $id > 0 ? $id : null;
    }

    public static function isCertificateService(int $serviceId): bool
    {
        return self::addonOf($serviceId) !== null;
    }

    public static function link(int $serviceId, int $hostingAddonId, array $extra = []): void
    {
        Request::where('serviceid', $serviceId)->where('name', self::LINK)->delete();
        Request::insert([
            'serviceid'  => $serviceId,
            'name'       => self::LINK,
            'request'    => json_encode(['addon' => $hostingAddonId] + $extra),
            'status'     => 'linked',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ---- module calls on an addon ---- //

    /**
     * The answer for a module call on a product addon; null when the call is for an ordinary service (the module then
     * runs as always). Never throws: WHMCS shows the returned text when it is not "success".
     */
    public static function guard(string $function, array $params)
    {
        $hostingAddonId = (int)($params['addonId'] ?? 0);
        if($hostingAddonId <= 0)
        {
            return null;
        }
        try
        {
            switch($function)
            {
                case 'CreateAccount':
                    return self::create($hostingAddonId, $params);
                case 'SuspendAccount':
                    return self::forward($hostingAddonId, 'ModuleSuspend', ['suspendreason' => (string)($params['suspendreason'] ?? 'Product addon suspended')]);
                case 'UnsuspendAccount':
                    return self::forward($hostingAddonId, 'ModuleUnsuspend');
                case 'TerminateAccount':
                    return self::forward($hostingAddonId, 'ModuleTerminate');
                case 'Renew':
                    return self::renew($hostingAddonId);
                case 'ChangePackage':
                case 'AdminServicesTabFieldsSave':
                    return 'success';
                case 'AdminCustomButtonArray':
                case 'ClientAreaCustomButtonArray':
                    return [];
                case 'AdminServicesTabFields':
                    return self::adminFields($hostingAddonId);
                case 'ClientArea':
                    return self::clientArea($hostingAddonId);
                default:
                    $serviceId = self::serviceOf($hostingAddonId);

                    return self::text('managed_elsewhere', ['service' => $serviceId ? '#' . $serviceId : '-']);
            }
        }
        catch(\Throwable $e)
        {
            \logActivity('Product addon #' . $hostingAddonId . ': ' . $function . ' failed: ' . $e->getMessage());

            return in_array($function, ['AdminCustomButtonArray', 'ClientAreaCustomButtonArray'], true) ? [] : $e->getMessage();
        }
    }

    /**
     * Sets up the certificate service of a hosting addon and runs the module's create on it. Called again (a failed
     * setup, the admin's "Create" on the addon) it reuses the service it made, unless that one has ended.
     */
    protected static function create(int $hostingAddonId, array $params): string
    {
        $addon = Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->first();
        if(!$addon)
        {
            return 'Product addon not found';
        }
        $productId = ProductAddons::productOf((int)$addon->addonid);
        if(!$productId)
        {
            return self::text('not_linked');
        }

        $serviceId = self::serviceOf($hostingAddonId);
        $status    = $serviceId ? (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domainstatus') : '';
        if($serviceId && $status === 'Active')
        {
            return 'success';
        }
        if(!$serviceId || !in_array($status, self::OPEN, true))
        {
            $serviceId = self::createService($addon, $productId);
        }

        $result = localAPI('ModuleCreate', ['serviceid' => $serviceId]);
        if(($result['result'] ?? '') !== 'success')
        {
            return 'The certificate service #' . $serviceId . ' could not be set up: ' . ($result['message'] ?? 'unknown error');
        }
        CertificateService::event($serviceId, 'info', '[Order] Certificate service of product addon #' . $hostingAddonId . ' (service #' . (int)$addon->hostingid . ') set up');

        return 'success';
    }

    /**
     * A service of the certificate product for the addon's client: billed on the addon, so free and without invoices.
     * $status: Pending for a new sale (the module's create activates it); the migration passes the addon's status.
     */
    public static function createService($addon, int $productId, string $status = 'Pending', array $linkExtra = []): int
    {
        $parentDomain = (string)Capsule::table('tblhosting')->where('id', (int)$addon->hostingid)->value('domain');
        $due          = $addon->nextduedate && $addon->nextduedate !== '0000-00-00' ? (string)$addon->nextduedate : date('Y-m-d');
        $serviceId    = (int)Capsule::table('tblhosting')->insertGetId([
            'userid'            => (int)$addon->userid,
            'orderid'           => (int)$addon->orderid,
            'packageid'         => $productId,
            'server'            => 0,
            'regdate'           => date('Y-m-d'),
            'domain'            => $parentDomain,
            'paymentmethod'     => (string)$addon->paymentmethod,
            'firstpaymentamount'=> 0,
            'amount'            => 0,
            'billingcycle'      => 'Free Account',
            'nextduedate'       => $due,
            'nextinvoicedate'   => $due,
            'domainstatus'      => in_array($status, self::OPEN, true) ? $status : 'Pending',
            'notes'             => 'Certificate of product addon #' . (int)$addon->id . ' (service #' . (int)$addon->hostingid . '). Billed on the product addon.',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        self::link($serviceId, (int)$addon->id, $linkExtra);

        return $serviceId;
    }

    /** Suspend / unsuspend / terminate of the addon, done on its certificate service */
    protected static function forward(int $hostingAddonId, string $command, array $extra = []): string
    {
        $serviceId = self::serviceOf($hostingAddonId);
        if(!$serviceId)
        {
            return 'success'; // never set up: nothing to do
        }
        $status = (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domainstatus');
        if(in_array($status, ['Terminated', 'Cancelled', 'Completed', 'Fraud'], true))
        {
            return 'success';
        }
        if(($command === 'ModuleSuspend' && $status === 'Suspended') || ($command === 'ModuleUnsuspend' && $status !== 'Suspended'))
        {
            return 'success';
        }
        $result = localAPI($command, ['serviceid' => $serviceId] + $extra);

        return ($result['result'] ?? '') === 'success' ? 'success' : 'Certificate service #' . $serviceId . ': ' . ($result['message'] ?? 'unknown error');
    }

    /**
     * The addon's renewal was paid: its certificate is renewed on the certificate service (always on that same
     * service - a renewal on a new service would need a billing cycle of its own). Its due date follows the addon's,
     * which WHMCS has just moved on.
     */
    protected static function renew(int $hostingAddonId): string
    {
        $serviceId = self::serviceOf($hostingAddonId);
        if(!$serviceId)
        {
            return 'The product addon has no certificate service to renew';
        }
        $due = (string)Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->value('nextduedate');
        if($due && $due !== '0000-00-00')
        {
            Capsule::table('tblhosting')->where('id', $serviceId)->update(['nextduedate' => $due, 'nextinvoicedate' => $due]);
        }
        if(!function_exists('ServerRenew'))
        {
            require_once ROOTDIR . '/includes/modulefunctions.php';
        }
        $result = ServerRenew($serviceId);

        return $result === 'success' || $result === true ? 'success' : (string)$result;
    }

    // ---- the certificate service follows into the addon ---- //

    /** The certificate's due date (set by the syncs) becomes the addon's, so WHMCS bills the renewal in time */
    public static function followDueDate(int $serviceId, string $date): void
    {
        $hostingAddonId = self::addonOf($serviceId);
        if(!$hostingAddonId || !$date || !strtotime($date))
        {
            return;
        }
        Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->whereNotIn('billingcycle', ['One Time', 'Free Account', 'Free'])
            ->update(['nextduedate' => $date, 'nextinvoicedate' => $date]);
    }

    /** The certificate service ended (revoked, refunded, expired, cancelled): the addon ends the same way */
    public static function followEnd(int $serviceId, string $whmcsStatus): void
    {
        $hostingAddonId = self::addonOf($serviceId);
        if(!$hostingAddonId)
        {
            return;
        }
        $update = ['status' => $whmcsStatus];
        if($whmcsStatus === 'Terminated')
        {
            $update['termination_date'] = date('Y-m-d');
        }
        $changed = Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->whereIn('status', ['Pending', 'Active', 'Suspended'])->update($update);
        if($changed)
        {
            \logActivity('Product addon #' . $hostingAddonId . ' ' . strtolower($whmcsStatus) . ': its certificate service #' . $serviceId . ' ended');
        }
    }

    // ---- pages ---- //

    protected static function clientArea(int $hostingAddonId): string
    {
        $serviceId = self::serviceOf($hostingAddonId);
        if(!$serviceId)
        {
            return '<div class="alert alert-info">' . htmlspecialchars(self::text('not_set_up')) . '</div>';
        }
        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first(['domain', 'domainstatus']);

        return '<div class="alert alert-info" style="text-align:left">' . htmlspecialchars(self::text('client_intro'))
            . ($service && $service->domain ? ' <strong>' . htmlspecialchars((string)$service->domain) . '</strong>' : '')
            . '</div><p><a class="btn btn-primary" href="clientarea.php?action=productdetails&amp;id=' . $serviceId . '">' . htmlspecialchars(self::text('client_button')) . '</a></p>';
    }

    protected static function adminFields(int $hostingAddonId): array
    {
        $serviceId = self::serviceOf($hostingAddonId);
        if(!$serviceId)
        {
            return [self::text('admin_label') => htmlspecialchars(self::text('not_set_up'))];
        }
        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first(['userid', 'domain', 'domainstatus']);

        return [self::text('admin_label') => '<a href="clientsservices.php?userid=' . (int)($service->userid ?? 0) . '&amp;id=' . $serviceId . '">'
            . htmlspecialchars('#' . $serviceId . ($service && $service->domain ? ' ' . $service->domain : '') . ' (' . ($service->domainstatus ?? '') . ')') . '</a>'];
    }

    const ENGLISH = [
        'not_linked'        => 'This product addon is not linked to a certificate product. Create it from the module\'s Products page ("Sell as product addon").',
        'not_set_up'        => 'The certificate is set up once the product addon is paid.',
        'client_intro'      => 'This SSL certificate is managed on its own page: configure it, follow its validation and download it there.',
        'client_button'     => 'Manage certificate',
        'admin_label'       => 'Certificate service',
        'managed_elsewhere' => 'This certificate is managed on its certificate service :service:.',
    ];

    /** Server module language (mainDomain-style keys under addonCertificate), English when not loaded */
    public static function text(string $key, array $replace = []): string
    {
        $text = '';
        try
        {
            if(class_exists('\ModulesGarden\Servers\TSSGGSModule\core\Lang'))
            {
                $text = html_entity_decode((string)\ModulesGarden\Servers\TSSGGSModule\core\Lang::absoluteT('addonCertificate', $key), ENT_QUOTES, 'UTF-8');
            }
        }
        catch(\Throwable $e)
        {
            $text = '';
        }
        if($text === '' || $text === $key || strpos($text, 'addonCertificate') !== false || strpos($text, '_LANG[') !== false)
        {
            $text = self::ENGLISH[$key] ?? $key;
        }
        foreach($replace as $name => $value)
        {
            $text = str_replace(':' . $name . ':', (string)$value, $text);
        }

        return $text;
    }
}
