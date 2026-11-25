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
        // the link text starts with {"addon":<id>, (link()): only that addon's rows are read
        $rows = Request::where('name', self::LINK)->where(function($q) use ($hostingAddonId) {
            $q->where('request', '{"addon":' . $hostingAddonId . '}')->orWhere('request', 'like', '{"addon":' . $hostingAddonId . ',%');
        })->orderBy('id', 'desc')->get(['serviceid', 'request']);
        foreach($rows as $row)
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
        $refused = ProductAddons::refusal($productId);
        if($refused !== '')
        {
            return $refused;
        }

        // the payment and the admin's "Create" can arrive at the same moment: one certificate service per addon
        $lock = 'tssggs_addon_' . $hostingAddonId;
        if((int)(Capsule::selectOne('SELECT GET_LOCK(?, 10) AS l', [$lock])->l ?? 0) !== 1)
        {
            return 'The product addon is being set up already. Try again in a moment.';
        }
        try
        {
            $serviceId = self::serviceOf($hostingAddonId);
            $status    = $serviceId ? (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domainstatus') : '';
            if($serviceId && in_array($status, ['Active', 'Suspended'], true))
            {
                return 'success'; // set up already (a suspended one is unsuspended, not created again)
            }
            if(!$serviceId || !in_array($status, self::OPEN, true))
            {
                $serviceId = self::createService($addon, $productId);
            }
        }
        finally
        {
            Capsule::select('SELECT RELEASE_LOCK(?)', [$lock]);
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
        // an ended certificate service (cancelled by the client or an admin, revoked, terminated) is never renewed: the
        // renewal would order a new certificate at the vendor for an addon that should have stopped
        $status = (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domainstatus');
        if(!in_array($status, self::OPEN, true))
        {
            self::followEnd($serviceId, in_array($status, ['Cancelled', 'Fraud'], true) ? 'Cancelled' : 'Terminated');

            return 'The certificate service #' . $serviceId . ' has ended (' . $status . '): the product addon is not renewed. Order a new certificate instead.';
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

    // ---- billing of a certificate service (it has none of its own) ---- //

    /**
     * The addon's billing cycle for a certificate service (the order term follows it: a 2-year addon orders a 2-year
     * certificate), null for an ordinary service
     */
    public static function billingCycleOf(int $serviceId): ?string
    {
        $hostingAddonId = self::addonOf($serviceId);
        $cycle          = $hostingAddonId ? (string)Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->value('billingcycle') : '';

        return $cycle !== '' ? $cycle : null;
    }

    /**
     * What the client pays for a certificate service and the invoice it was paid with - the addon's - for the module's
     * cost / profit records; null for an ordinary service
     */
    public static function billing(int $serviceId): ?array
    {
        $hostingAddonId = self::addonOf($serviceId);
        if(!$hostingAddonId)
        {
            return null;
        }
        $addon   = Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->first(['recurring', 'setupfee']);
        $invoice = (int)Capsule::table('tblinvoiceitems')->where('type', 'Addon')->where('relid', $hostingAddonId)->orderBy('id', 'desc')->value('invoiceid');

        return ['amount' => (float)($addon->recurring ?? 0), 'invoiceid' => $invoice];
    }

    // ---- keeping addon and certificate service in step (cron) ---- //

    /**
     * Ends that happened outside the module's own calls: the client cancelled the certificate service, an admin
     * terminated it or changed the addon by hand, the addon was deleted. The side still running follows the ended one -
     * by status only, nothing is ordered or cancelled at the vendor here. Returns what was changed.
     */
    public static function reconcile(): array
    {
        $changes = [];
        $links   = [];
        foreach(Request::where('name', self::LINK)->get(['serviceid', 'request']) as $row)
        {
            $links[(int)$row->serviceid] = (int)((json_decode((string)$row->request, true) ?: [])['addon'] ?? 0);
        }
        if(!$links)
        {
            return $changes;
        }
        // the statuses of all linked services and addons in two queries (this runs with every order status sync)
        $services = Capsule::table('tblhosting')->whereIn('id', array_keys($links))->pluck('domainstatus', 'id')->all();
        $addons   = Capsule::table('tblhostingaddons')->whereIn('id', array_values(array_filter($links)))->pluck('status', 'id')->all();
        foreach($links as $serviceId => $hostingAddonId)
        {
            $service = $services[$serviceId] ?? null;
            $addon   = $hostingAddonId ? ($addons[$hostingAddonId] ?? null) : null;
            if($service === null)
            {
                continue;
            }
            $serviceOpen = in_array($service, self::OPEN, true);
            $addonOpen   = $addon !== null && in_array($addon, self::OPEN, true);
            if(!$serviceOpen && $addonOpen && self::serviceOf($hostingAddonId) === $serviceId)
            {
                self::followEnd($serviceId, in_array($service, ['Cancelled', 'Fraud'], true) ? 'Cancelled' : 'Terminated');
                $changes[] = 'addon #' . $hostingAddonId . ' follows service #' . $serviceId . ' (' . $service . ')';
            }
            elseif($serviceOpen && !$addonOpen)
            {
                // the addon was cancelled, terminated or deleted without the module: its certificate is billed no more
                $to = $addon === null || in_array($addon, ['Cancelled', 'Fraud'], true) ? 'Cancelled' : 'Terminated';
                Capsule::table('tblhosting')->where('id', $serviceId)->whereIn('domainstatus', self::OPEN)->update(['domainstatus' => $to]
                    + ($to === 'Terminated' ? ['termination_date' => date('Y-m-d')] : []));
                \logActivity('Certificate service #' . $serviceId . ' ' . strtolower($to) . ': its product addon #' . $hostingAddonId . ' ' . ($addon === null ? 'was deleted' : 'is ' . $addon));
                $changes[] = 'service #' . $serviceId . ' follows addon #' . $hostingAddonId . ' (' . ($addon ?? 'deleted') . ')';
            }
        }

        return $changes;
    }

    // ---- pages ---- //

    /** Shown on top of a certificate service's client page: it is billed with a product addon of another service */
    public static function clientNotice(int $serviceId): string
    {
        $hostingAddonId = self::addonOf($serviceId);
        if(!$hostingAddonId)
        {
            return '';
        }
        $addon  = Capsule::table('tblhostingaddons')->where('id', $hostingAddonId)->first(['hostingid', 'addonid', 'name']);
        $name   = $addon ? ((string)$addon->name ?: (string)Capsule::table('tbladdons')->where('id', (int)$addon->addonid)->value('name')) : '';
        $domain = $addon ? (string)Capsule::table('tblhosting')->where('id', (int)$addon->hostingid)->value('domain') : '';

        return self::text('service_notice', ['addon' => $name, 'service' => $domain !== '' ? $domain : '#' . (int)($addon->hostingid ?? 0)]);
    }

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
        'service_notice'    => 'This certificate is billed with the product addon ":addon:" of :service:. It is renewed and cancelled there.',
        'upgrade_blocked'   => 'This certificate is billed with a product addon: it can not be upgraded and no domains can be added to it. Order a certificate with more domains instead.',
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
