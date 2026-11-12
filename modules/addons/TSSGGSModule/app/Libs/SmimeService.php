<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;

/**
 * S/MIME (email signing) certificates of GoGetSSL and TheSSLStore, ordered through the gateway (/certificates/smime).
 *
 * Stored order (see GatewayOrderService) plus: emails [{email, approved}], csrProvided, remindersSent,
 * enrollmentLink + migrated (TheSSLStore invite orders migrated from the legacy module).
 * tblhosting.domain = the first email address (the certificate subject).
 */
class SmimeService extends GatewayOrderService
{
    const CATEGORY     = 'smime';
    const REQUEST_NAME = 'smime';
    const LABEL        = 'S/MIME';

    // most addresses the order form takes (subject + alternative names); the gateway enforces the product's own limit
    const MAX_EMAILS = 10;

    // contact fields the gateway takes for organization-validated products (same as GoGetSSL API v2)
    const ORGANIZATION_FIELDS  = ['name', 'division', 'duns', 'lei', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
    const ADMINISTRATOR_FIELDS = ['first_name', 'last_name', 'title', 'organization', 'email', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
    const ORGANIZATION_REQUIRED  = ['name', 'phone', 'country', 'city', 'address_line_1'];
    // TheSSLStore also needs the organization's state / region ("stateorprovincename") for OV S/MIME
    const ORGANIZATION_REQUIRED_TSS = ['region'];
    const ADMINISTRATOR_REQUIRED = ['first_name', 'last_name', 'email', 'phone', 'country'];

    // days after the order on which the client is reminded to confirm the mailbox
    const REMINDER_DAYS = [2, 7];

    public static function configurationTemplate()
    {
        return EmailTemplates::SMIME_CONFIGURATION_TEMPLATE;
    }

    public static function isSmimeWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isSmimeService($serviceId)
    {
        return static::isService($serviceId);
    }

    /**
     * Gateway products the module can sell: the S/MIME products of GoGetSSL and TheSSLStore (the gateway takes the same
     * order for both: product, term, emails, optional CSR and contacts)
     */
    public static function isSupportedRemoteProduct($vendor, $category)
    {
        return strtolower((string)$category) === self::CATEGORY && in_array(strtoupper((string)$vendor), ['GGS', 'TSS'], true);
    }

    /**
     * OV products (organization + administrator contacts): the gateway product says so, else the product name
     */
    public static function requiresOrganization($whmcsProductId)
    {
        $remote     = static::remoteProduct($whmcsProductId);
        $validation = $remote ? strtoupper((string)$remote->validation) : '';
        if($validation !== '')
        {
            return in_array($validation, ['OV', 'EV'], true);
        }

        $name = $remote ? (string)$remote->name : (string)Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->value('name');

        return (bool)preg_match('/organi[sz]ation|business|\bOV[A-Z]?\b|sponsor/i', $name);
    }

    /**
     * Organization fields the order needs for the product's vendor
     */
    public static function organizationRequired($whmcsProductId)
    {
        $vendor = strtoupper((string)Capsule::table('tblproducts')->where('id', (int)$whmcsProductId)->value('configoption1'));

        return array_merge(self::ORGANIZATION_REQUIRED, $vendor === 'TSS' ? self::ORGANIZATION_REQUIRED_TSS : []);
    }

    /**
     * Places the order at the gateway.
     *
     * @param array $input emails (validated, first = subject), csr (optional), organization / administrator (OV products,
     *                     validated by contacts())
     */
    public static function create($serviceId, array $input)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::termForBillingCycle($service->billingcycle, static::availableTerms($service->packageid));
        $emails    = array_values($input['emails']);

        $payload = [
            'product' => ['id' => $productId, 'term' => $term],
            'emails'  => $emails,
        ];
        if(!empty($input['csr']))
        {
            $payload['csr'] = trim((string)$input['csr']);
        }
        if(!empty($input['organization']) || !empty($input['administrator']))
        {
            $payload['contacts'] = [
                'organization'  => $input['organization'],
                'administrator' => $input['administrator'],
            ];
        }

        $response = CertificateService::api($serviceId)->createSmime($payload);

        return static::saveCreated($serviceId, $ssl, [
            'term'        => $term,
            'csrProvided' => !empty($payload['csr']),
            'emails'      => array_map(function($email) { return ['email' => $email, 'approved' => false]; }, $emails),
        ], $response, $emails[0], 'S/MIME certificate ordered for ' . implode(', ', $emails));
    }

    protected static function applyItem(array $stored, array $item)
    {
        if(isset($item['emails']) && is_array($item['emails']) && $item['emails'])
        {
            $stored['emails'] = static::normalizeEmails($item['emails']);
        }

        return $stored;
    }

    /**
     * "S/MIME Certificate Issued" e-mail: how the client gets the certificate
     */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
        if(($stored['status'] ?? '') !== 'active')
        {
            return;
        }

        try
        {
            EmailTemplates::sendEmail(EmailTemplates::SMIME_ISSUED_TEMPLATE, $serviceId, [
                'smime_emails'       => implode(', ', array_column((array)($stored['emails'] ?? []), 'email')),
                'smime_valid_till'   => !empty($stored['validTill']) ? date('F j, Y', strtotime($stored['validTill'])) : '',
                'smime_csr_provided' => !empty($stored['csrProvided']) ? 1 : 0,
            ]);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', 'S/MIME issued e-mail not sent: ' . $e->getMessage());
        }
    }

    protected static function afterCronRefresh($serviceId)
    {
        static::remindIfUnconfirmed($serviceId);
    }

    protected static function previousEntry(array $stored)
    {
        return ['emails' => array_column((array)($stored['emails'] ?? []), 'email')];
    }

    /** a migrated invite order the client has not enrolled yet: the gateway can not read it until then */
    protected static function quietFailure(array $stored)
    {
        return ($stored['status'] ?? '') === 'invited';
    }

    /**
     * "S/MIME Mailbox Not Confirmed" e-mail on day 2 and day 7 after the order while an address is not confirmed
     * (one e-mail per run; the days already passed are marked as sent)
     */
    public static function remindIfUnconfirmed($serviceId)
    {
        $stored = static::stored($serviceId);
        if(!in_array($stored['status'] ?? '', ['pending', 'processing'], true) || empty($stored['orderedAt']))
        {
            return false;
        }
        $pending = [];
        foreach((array)($stored['emails'] ?? []) as $entry)
        {
            if(empty($entry['approved']))
            {
                $pending[] = $entry['email'];
            }
        }
        $days = (int)floor((time() - strtotime($stored['orderedAt'])) / 86400);
        $sent = (array)($stored['remindersSent'] ?? []);
        $due  = array_values(array_filter(self::REMINDER_DAYS, function($day) use ($days, $sent) { return $days >= $day && !in_array($day, $sent, true); }));
        if(!$pending || !$due)
        {
            return false;
        }

        EmailTemplates::sendEmail(EmailTemplates::SMIME_REMINDER_TEMPLATE, $serviceId, [
            'smime_pending_emails' => implode(', ', $pending),
            'smime_ordered_date'   => date('F j, Y', strtotime($stored['orderedAt'])),
        ]);
        $stored['remindersSent'] = array_values(array_unique(array_merge($sent, $due)));
        static::save($serviceId, $stored);
        CertificateService::event($serviceId, 'info', 'S/MIME reminder sent: mailbox not confirmed (' . implode(', ', $pending) . ')');

        return true;
    }

    /**
     * [['email' => .., 'approved' => bool], ...] from the gateway's list (strings or objects)
     */
    public static function normalizeEmails($emails)
    {
        $result = [];
        foreach((array)$emails as $entry)
        {
            if(is_string($entry))
            {
                $result[] = ['email' => $entry, 'approved' => false];
                continue;
            }
            $entry = (array)$entry;
            $email = $entry['email'] ?? ($entry['name'] ?? '');
            if($email !== '')
            {
                $result[] = ['email' => (string)$email, 'approved' => !empty($entry['approved'])];
            }
        }

        return $result;
    }

    /**
     * Unique, lower-case addresses from the form (first = subject). Invalid ones are returned in $invalid.
     */
    public static function parseEmails(array $values, &$invalid = [])
    {
        $result  = [];
        $invalid = [];
        foreach($values as $value)
        {
            foreach(preg_split('/[\s,;]+/', (string)$value, -1, PREG_SPLIT_NO_EMPTY) as $email)
            {
                $email = strtolower(trim($email));
                if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                {
                    $invalid[] = $email;
                }
                elseif(!in_array($email, $result, true))
                {
                    $result[] = $email;
                }
            }
        }

        return $result;
    }

    /**
     * Organization and administrator contacts with every field the gateway takes (empty = null), country codes
     * upper-case. Returns the first missing required field as [group, field] in $missing.
     */
    public static function contacts(array $organization, array $administrator, &$missing = null, array $organizationRequired = self::ORGANIZATION_REQUIRED)
    {
        $missing = null;
        $org     = [];
        foreach(self::ORGANIZATION_FIELDS as $field)
        {
            $value       = trim((string)($organization[$field] ?? ''));
            $org[$field] = $value !== '' ? $value : null;
        }
        $admin = [];
        foreach(self::ADMINISTRATOR_FIELDS as $field)
        {
            $value         = trim((string)($administrator[$field] ?? ''));
            $admin[$field] = $value !== '' ? $value : null;
        }
        if($admin['organization'] === null)
        {
            $admin['organization'] = $org['name'];
        }

        foreach($organizationRequired as $field)
        {
            if($org[$field] === null)
            {
                $missing = ['org', $field];
                break;
            }
        }
        if(!$missing)
        {
            foreach(self::ADMINISTRATOR_REQUIRED as $field)
            {
                if($admin[$field] === null)
                {
                    $missing = ['admin', $field];
                    break;
                }
            }
        }

        $org['country']   = $org['country'] !== null ? strtoupper($org['country']) : null;
        $admin['country'] = $admin['country'] !== null ? strtoupper($admin['country']) : null;

        return ['organization' => $org, 'administrator' => $admin];
    }
}
