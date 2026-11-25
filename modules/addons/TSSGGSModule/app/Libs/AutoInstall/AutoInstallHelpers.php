<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\AutoInstall;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Libs\Logs\Logger;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;

/**
 * SSL AutoInstall (via cPanel).
 *
 * Per-service configuration (install domain, encrypted private key, summary status) is kept in the Requests table
 * (name = 'autoinstall'). Work is done by cron through the AutoInstallQueue tasks:
 *  - validate: watches the order, uploads HTTP/HTTPS validation files to cPanel, switches DNS validation to email
 *              when it can not be completed, queues "install" once the certificate is issued,
 *  - install:  installs the certificate in cPanel and notifies the client,
 *  - reissue:  reissues the certificate (same CSR / private key) shortly before it expires within a subscription.
 * Every step is written to the AutoInstall service log (client area + admin area).
 */
class AutoInstallHelpers
{
    use Concerns\AutoInstallEvents;
    use Concerns\AutoInstallTasks;
    use Concerns\AutoInstallValidation;

    const REQUEST_NAME = 'autoinstall';

    const STATUS_AWAITING  = 'awaiting_validation';

    const STATUS_INSTALLED = 'installed';

    const STATUS_FAILED    = 'failed';

    const STATUS_DISABLED  = 'disabled';

    /**
     * DNS validation: the record is created in the client's cPanel zone automatically. When the validation is not
     * completed in time, the method is switched to email and the client is notified (same rules as SSLCENTERWHMCS):
     *  - wildcard: after the "Wildcard DNS Validation Timeout (minutes)" SSL setting since the order was placed,
     *  - other:    after DNS_MAX_VALIDATE_ATTEMPTS validation checks (cron runs).
     */
    const DEFAULT_WILDCARD_DNS_TIMEOUT_MINUTES = 65;

    const DNS_MAX_VALIDATE_ATTEMPTS       = 48;

    const DNS_RECORD_TTL                  = 3600;

    const VALIDATION_WARNING_AFTER_CHECKS = 5;

    /** Default of the "Auto Reissue Days Before Expiration" SSL setting (0 = disabled) */
    const DEFAULT_AUTO_REISSUE_DAYS_BEFORE_EXPIRE = 7;

    const MAX_UPLOAD_ATTEMPTS             = 10;

    const MAX_DNS_SWITCH_ATTEMPTS         = 5;

    const SAME_CERT_MAX_CHECKS            = 288;

    const FINAL_ORDER_STATUSES = ['cancelled', 'expired', 'rejected', 'failed', 'revoked', 'refunded'];

    // ------------------------------------------------------------------ //
    // Per-service configuration
    // ------------------------------------------------------------------ //

    public static function create($serviceId, array $data)
    {
        unset($data['status']);
        Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->delete();
        Request::insert([
            'serviceid'  => $serviceId,
            'name'       => self::REQUEST_NAME,
            'request'    => \encrypt(json_encode($data)),
            'status'     => self::STATUS_AWAITING,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function get($serviceId)
    {
        $row = Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->first();
        if(!$row)
        {
            return null;
        }

        $data           = json_decode(\decrypt($row->request), true) ?: [];
        $data['status'] = $row->status;

        return $data;
    }

    public static function save($serviceId, array $data, $status)
    {
        unset($data['status']);
        Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->update([
            'request'    => \encrypt(json_encode($data)),
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // the admin report (M-21): the running job of the service ends here
        if($status === self::STATUS_INSTALLED)
        {
            AutoInstallReport::finish($serviceId, AutoInstallReport::INSTALLED);
        }
        elseif($status === self::STATUS_FAILED)
        {
            AutoInstallReport::finish($serviceId, AutoInstallReport::FAILED, (string)($data['last_error'] ?? ''));
        }
        elseif($status === self::STATUS_DISABLED)
        {
            AutoInstallReport::finish($serviceId, AutoInstallReport::STOPPED, 'AutoInstall turned off');
        }
    }

    /**
     * Data for the client / admin area (no private key).
     */
    public static function getStatusForView($serviceId, $logsLimit = 10)
    {
        $data = self::get($serviceId);
        if(!$data)
        {
            return null;
        }

        $logs = [];
        try
        {
            foreach(AutoInstallQueue::getLogs($serviceId, $logsLimit) as $log)
            {
                $logs[] = ['date' => $log->created_at, 'type' => $log->type, 'message' => $log->message];
            }
        }
        catch(\Exception $e)
        {
        }

        return [
            'status'       => $data['status'],
            'domain'       => isset($data['domain']) ? $data['domain'] : '',
            'last_error'   => isset($data['last_error']) ? $data['last_error'] : '',
            'installed_at' => isset($data['installed_at']) ? $data['installed_at'] : '',
            'valid_till'   => isset($data['valid_till']) ? $data['valid_till'] : '',
            'logs'         => $logs,
        ];
    }

    public static function log($clientId, $serviceId, $type, $message)
    {
        AutoInstallQueue::addLog($clientId, $serviceId, $type, $message);

        try
        {
            if($type === 'error')
            {
                Logger::error('[AutoInstall] ' . $message, ['service' => $serviceId]);
            }
            else
            {
                Logger::info('[AutoInstall] ' . $message, ['service' => $serviceId]);
            }
        }
        catch(\Throwable $e)
        {
            // Logger is not available outside of the addon context - the service log above is enough
        }
    }

    // ------------------------------------------------------------------ //
    // Events
    // ------------------------------------------------------------------ //

    public static function getApi($serviceId)
    {
        // one place builds the gateway client (and reports missing credentials)
        return CertificateService::api($serviceId);
    }

    protected static function certificateName(array $name)
    {
        ksort($name);

        return json_encode($name);
    }

    protected static function certificateDomains(array $parsed)
    {
        $names = isset($parsed['subject']['CN']) ? [strtolower((string)$parsed['subject']['CN'])] : [];

        if(!empty($parsed['extensions']['subjectAltName']))
        {
            foreach(explode(',', $parsed['extensions']['subjectAltName']) as $san)
            {
                $san = trim($san);
                if(stripos($san, 'DNS:') === 0)
                {
                    $names[] = strtolower(trim(substr($san, 4)));
                }
            }
        }

        return $names;
    }
}
