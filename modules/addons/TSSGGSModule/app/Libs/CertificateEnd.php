<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;

/**
 * What a certificate's end at the vendor does to its WHMCS service (M-13). Called by the syncs when the status they
 * read differs from the one stored before, so each end is handled once:
 *  - revoked: service Cancelled (no more invoices, reissue / renew gone), "Certificate Revoked" e-mail, activity log
 *  - refunded: service Cancelled, activity log (the refund was arranged with the client: no e-mail)
 *  - expired (and filed): service Terminated with today's date, "Certificate Expired" e-mail (expired only)
 *  - cancelled / rejected: service Cancelled ($withCancelled: the SSL sync always did this, the other types do not)
 * Only a service that is still running (Pending, Active, Suspended) changes, and only then is the client told: one
 * that was renewed, ended or cancelled by an admin meanwhile is left as it is.
 */
class CertificateEnd
{
    const RUNNING = ['Pending', 'Active', 'Suspended'];

    protected static $validTill = '';

    /** @param string $validTill end of the certificate when the caller knows it (S/MIME, code / document signing) */
    public static function apply(int $serviceId, string $previousStatus, string $status, bool $withCancelled = true, string $validTill = ''): void
    {
        self::$validTill = $validTill;
        if($status === '' || $status === $previousStatus)
        {
            return;
        }
        $cancel = ['revoked', 'refunded'];
        if($withCancelled)
        {
            $cancel = array_merge($cancel, ['cancelled', 'rejected']);
        }

        if(in_array($status, $cancel, true))
        {
            $changed = Capsule::table('tblhosting')->where('id', $serviceId)->whereIn('domainstatus', self::RUNNING)->update(['domainstatus' => 'Cancelled']);
            if($changed)
            {
                // a product addon's certificate: the addon stops being billed too
                AddonCertificates::followEnd($serviceId, 'Cancelled');
            }
            if($changed && in_array($status, ['revoked', 'refunded'], true))
            {
                \logActivity('Service #' . $serviceId . ' cancelled: the certificate was ' . $status . ' at the certificate authority', self::userId($serviceId));
                CertificateService::event($serviceId, 'error', '[Status] The certificate was ' . $status . ': service cancelled');
                if($status === 'revoked')
                {
                    self::send(EmailTemplates::CERT_REVOKED_TEMPLATE, $serviceId);
                }
            }

            return;
        }

        if(in_array($status, ['expired', 'filed'], true))
        {
            $changed = Capsule::table('tblhosting')->where('id', $serviceId)->whereIn('domainstatus', self::RUNNING)->update([
                'domainstatus'     => 'Terminated',
                'termination_date' => date('Y-m-d'),
            ]);
            if($changed)
            {
                AddonCertificates::followEnd($serviceId, 'Terminated');
            }
            if($changed && $status === 'expired')
            {
                \logActivity('Service #' . $serviceId . ' terminated: the certificate expired', self::userId($serviceId));
                self::send(EmailTemplates::CERT_EXPIRED_TEMPLATE, $serviceId);
            }
        }
    }

    /** Never stops a sync: the status change is saved whatever happens to the e-mail */
    protected static function send(string $template, int $serviceId): void
    {
        try
        {
            EmailTemplates::sendEmail($template, $serviceId, self::fields($serviceId));
        }
        catch(\Throwable $e)
        {
            \logActivity('E-mail "' . $template . '" for service #' . $serviceId . ' not sent: ' . $e->getMessage());
        }
    }

    /** cert_expiry and serial_number of the last certificate the module stored for the service ('' when unknown) */
    public static function fields(int $serviceId): array
    {
        $systemUrl = rtrim((string)Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        $fields    = ['cert_expiry' => '', 'serial_number' => '', 'support_url' => $systemUrl . '/submitticket.php'];
        try
        {
            $stored = CertificateService::stored($serviceId);
            $end    = (string)($stored['orderFiles']['validity']['end'] ?? '');
            // the client's certificate, not one of the CA bundle
            $cert = false;
            array_walk_recursive($stored, function($value) use (&$cert) {
                if($cert || !is_string($value) || !preg_match_all('/-----BEGIN CERTIFICATE-----.+?-----END CERTIFICATE-----/s', $value, $m))
                {
                    return;
                }
                foreach($m[0] as $pem)
                {
                    $parsed = @openssl_x509_parse($pem);
                    if($parsed && stripos((string)($parsed['extensions']['basicConstraints'] ?? ''), 'CA:TRUE') === false)
                    {
                        $cert = $parsed;
                        return;
                    }
                }
            });
            if($cert)
            {
                $fields['serial_number'] = strtoupper((string)($cert['serialNumberHex'] ?? ''));
                $end = $end !== '' ? $end : date('Y-m-d', (int)$cert['validTo_time_t']);
            }
            $end = $end !== '' ? $end : self::$validTill;
            if($end !== '' && strtotime($end))
            {
                $fields['cert_expiry'] = date('Y-m-d', strtotime($end));
            }
        }
        catch(\Throwable $e)
        {
        }

        return $fields;
    }

    protected static function userId(int $serviceId): int
    {
        return (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('userid');
    }
}
