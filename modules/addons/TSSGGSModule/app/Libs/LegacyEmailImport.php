<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Opt-in copy of the e-mail templates a reseller customised in the legacy GoGetSSL / TheSSLStore modules into the
 * unified module's templates (decision D5). Nothing is copied unless the administrator selects it.
 */
class LegacyEmailImport
{
    /** legacy WHMCS template name => unified template key, per source module */
    const MAP = [
        'ggs' => [
            'SSLCenter - Configuration Required'          => 'Configuration Required',
            'SSLCenter - Service Expiration'              => 'Service Expiration',
            'SSLCenter - Send Certificate'                => 'Send Certificate',
            'SSLCenter - Renewal'                         => 'Renewal',
            'SSLCenter - Reissue'                         => 'Reissue',
            'SSLCenter - ACME Subscription Configuration' => 'ACME Subscription Configuration',
            'SSLCenter - ACME Subscription Expiration'    => 'ACME Subscription Expiration',
            'SSLCenter - ACME Subscription Renewal'       => 'ACME Subscription Renewal',
            'SSLCenter - Certificate Installed'           => 'Certificate Installed',
            'SSLCenter - Renewal Complete'                => 'Renewal Complete',
            'SSLCenter - DNS Validation Switched to Email' => 'DNS Validation Switched to Email',
            'SSLCenter - Auto Reissue Failed'             => 'Auto Reissue Failed',
            'SSLCenter - Validation Warning'              => 'Validation Warning',
            'SSLCenter - Renewal Started'                 => 'Renewal Started',
            'SSLCenter - Certificate Autorenewed'         => 'Certificate Autorenewed',
        ],
        'tss' => [
            'Your SSL Certificate - Enrollment steps'              => 'Configuration Required',
            'Your SSL Certificate - Expiration Reminder Email'     => 'Service Expiration',
            'Digicert Reissue Reminder'                            => 'Reissue',
            'Comodo Reissue Reminder'                              => 'Reissue',
            'Subscription – Configuration'                         => 'ACME Subscription Configuration',
            'Subscription – Expiration'                            => 'ACME Subscription Expiration',
            'Subscription – Renewal'                               => 'ACME Subscription Renewal',
            'AutoInstall SSL – Installation Complete'              => 'Certificate Installed',
            'AutoInstall SSL – Certificate Renewal Complete'       => 'Renewal Complete',
            'AutoInstall SSL – DNS Validation Failed'              => 'DNS Validation Switched to Email',
            'AutoInstall SSL – Reissue Failed'                     => 'Auto Reissue Failed',
            'AutoInstall SSL – Domain Validation Required'         => 'Validation Warning',
            'AutoInstall SSL – Certificate Renewal Initiated'      => 'Renewal Started',
            'AutoInstall SSL – Certificate Autorenewed'            => 'Certificate Autorenewed',
        ],
    ];

    /** Variables WHMCS fills for every e-mail of a service (prefixes) and a few global ones */
    const STANDARD_PREFIXES = ['client_', 'service_', 'company_', 'whmcs_', 'email_'];
    const STANDARD_NAMES    = ['signature', 'date', 'time', 'charset', 'currency_code', 'currency_prefix', 'currency_suffix'];

    /**
     * Legacy templates found, with what importing them would do
     * @return array[] ['id' => 'ggs|<legacy name>', 'source', 'legacy', 'key', 'languages', 'same', 'replaces_edited', 'unknown']
     */
    public static function candidates()
    {
        EmailManager::ensureAll();

        $found = [];
        foreach(self::MAP as $source => $names)
        {
            foreach($names as $legacy => $key)
            {
                $rows = Capsule::table(EmailManager::TABLE)->where('name', $legacy)->get(['language', 'subject', 'message']);
                if(!$rows->count() || !EmailCatalog::find($key))
                {
                    continue;
                }

                $default = null;
                foreach($rows as $row)
                {
                    if((string)$row->language === '')
                    {
                        $default = $row;
                    }
                }
                $current = Capsule::table(EmailManager::TABLE)->where('name', EmailManager::name($key))->where('language', '')->first();
                $catalog = EmailCatalog::find($key);

                $found[] = [
                    'id'              => $source . '|' . $legacy,
                    'source'          => $source,
                    'legacy'          => $legacy,
                    'key'             => $key,
                    'languages'       => count($rows) - ($default ? 1 : 0),
                    'same'            => $default && $current && self::normalize($default->message) === self::normalize($current->message) && self::normalize($default->subject) === self::normalize($current->subject),
                    'replaces_edited' => $current && (self::normalize($current->message) !== self::normalize($catalog['message']) || self::normalize($current->subject) !== self::normalize($catalog['subject'])),
                    'unknown'         => self::unknownFields($rows, $catalog),
                ];
            }
        }

        return $found;
    }

    /**
     * Copies the selected legacy templates (all their languages); returns ['copied' => n, 'created_ids' => [..]]
     * - rows created by the copy, so a migration rollback can remove them
     */
    public static function import(array $ids)
    {
        $copied  = 0;
        $created = [];
        $wanted  = array_flip($ids);
        $done    = [];

        foreach(self::candidates() as $candidate)
        {
            // two legacy templates can map to one (DigiCert / Comodo reissue reminder): the first selected wins
            if(!isset($wanted[$candidate['id']]) || isset($done[$candidate['key']]))
            {
                continue;
            }
            $done[$candidate['key']] = true;

            $target = EmailManager::name($candidate['key']);
            foreach(Capsule::table(EmailManager::TABLE)->where('name', $candidate['legacy'])->get() as $row)
            {
                $existing = Capsule::table(EmailManager::TABLE)->where('name', $target)->where('language', (string)$row->language)->first();
                $values   = [
                    'type'          => 'product', 'subject' => $row->subject, 'message' => $row->message, 'attachments' => (string)$row->attachments,
                    'fromname'      => (string)$row->fromname, 'fromemail' => (string)$row->fromemail, 'disabled' => (int)$row->disabled, 'custom' => 1,
                    'copyto'        => (string)$row->copyto, 'blind_copy_to' => (string)$row->blind_copy_to, 'plaintext' => (int)$row->plaintext, 'updated_at' => date('Y-m-d H:i:s'),
                ];
                if($existing)
                {
                    Capsule::table(EmailManager::TABLE)->where('id', $existing->id)->update($values);
                }
                else
                {
                    $created[] = Capsule::table(EmailManager::TABLE)->insertGetId(['name' => $target, 'language' => (string)$row->language, 'created_at' => date('Y-m-d H:i:s')] + $values);
                }
            }
            $copied++;
        }

        return ['copied' => $copied, 'created_ids' => $created];
    }

    /**
     * Variables of the legacy text that the unified module does not fill: [name, ...]
     */
    protected static function unknownFields($rows, array $catalog)
    {
        $allowed = array_merge($catalog['fields'], EmailCatalog::STANDARD_FIELDS, self::STANDARD_NAMES);
        $unknown = [];
        foreach($rows as $row)
        {
            preg_match_all('/\{[^{}]*\}/', $row->subject . ' ' . $row->message, $blocks);
            foreach($blocks[0] as $block)
            {
                preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $block, $variables);
                foreach($variables[1] as $variable)
                {
                    $known = in_array($variable, $allowed, true);
                    foreach(self::STANDARD_PREFIXES as $prefix)
                    {
                        $known = $known || strpos($variable, $prefix) === 0;
                    }
                    if(!$known)
                    {
                        $unknown[$variable] = $variable;
                    }
                }
            }
        }

        return array_values($unknown);
    }

    protected static function normalize($text)
    {
        return trim(preg_replace('/\s+/', ' ', (string)$text));
    }
}
