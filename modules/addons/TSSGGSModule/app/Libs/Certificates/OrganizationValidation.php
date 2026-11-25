<?php

namespace TSSGGSModule\App\Libs\Certificates;

use TSSGGSModule\App\Helpers\EmailTemplates;
use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\ProductLines\GatewayOrderService;

/**
 * OV / EV orders: the certificate authority verifies the organization before it issues the certificate and contacts
 * the administrator by e-mail and phone. The client is told so once per order ("Organization Validation Required"),
 * for every product that needs it: SSL (OV, EV), S/MIME (OV), code signing and document signing.
 */
class OrganizationValidation
{
    /** Requests row that remembers the orders the e-mail went out for (one per order item) */
    const SENT_MARK = 'orgValidationMail';

    /** gateway categories validated against an organization even where the catalogue gives no validation type */
    const ALWAYS_ORGANIZATION = ['cs', 'ds'];

    /**
     * 'OV' / 'EV' for a product that needs organization validation, '' when it does not (DV, subscriptions, other
     * services, or a product the catalogue does not know)
     */
    public static function type(int $serviceId): string
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid');
        $remote    = $packageId ? GatewayOrderService::remoteProduct($packageId) : null;
        if(!$remote)
        {
            return '';
        }
        $validation = strtoupper(trim((string)$remote->validation));
        if(in_array($validation, ['OV', 'EV'], true))
        {
            return $validation;
        }
        $raw = is_array($remote->rawData) ? $remote->rawData : [];

        return in_array((string)($raw['category'] ?? ''), self::ALWAYS_ORGANIZATION, true) && $validation === '' ? 'OV' : '';
    }

    /**
     * Sends the e-mail for the order just placed (item $itemId). Never throws: the order is placed whatever happens here.
     */
    public static function notify(int $serviceId, $itemId, bool $renewal = false): void
    {
        try
        {
            $type = self::type($serviceId);
            if($type === '' || self::alreadySent($serviceId, (string)$itemId))
            {
                return;
            }
            EmailTemplates::sendEmail(EmailTemplates::ORG_VALIDATION_TEMPLATE, $serviceId, [
                'validation_type' => $type,
                'is_renewal'      => $renewal ? 1 : 0,
            ]);
            Request::insert([
                'serviceid'  => $serviceId,
                'name'       => self::SENT_MARK,
                'request'    => (string)$itemId,
                'status'     => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            CertificateService::event($serviceId, 'info', '[Order] ' . $type . ' order: the client was told the certificate authority will verify the organization');
        }
        catch(\Throwable $e)
        {
            \logActivity('Organization validation e-mail for service #' . $serviceId . ' not sent: ' . $e->getMessage());
        }
    }

    protected static function alreadySent(int $serviceId, string $itemId): bool
    {
        return $itemId !== '' && Request::where('serviceid', $serviceId)->where('name', self::SENT_MARK)->where('request', $itemId)->exists();
    }
}
