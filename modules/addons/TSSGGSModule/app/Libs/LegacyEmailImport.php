<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Carries the e-mail texts of the legacy GoGetSSL / TheSSLStore modules over to the unified module's templates.
 * The migration does it automatically for every text that is safe to send (auto()); the Emails page lists the rest
 * for a manual, per template decision.
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

    /** Legacy merge field => the unified field that carries the same value (used when the legacy name is not filled) */
    const ALIASES = [
        'order_link'    => 'order_url',
        'reissue_link'  => 'order_url',
        'reminder_days' => 'expireDaysLeft',
        'common_name'   => 'service_domain',
        'sso_link'      => 'order_url',
    ];

    /** Variables WHMCS fills for every e-mail of a service (prefixes) and a few global ones */
    const STANDARD_PREFIXES = ['client_', 'service_', 'company_', 'whmcs_', 'email_'];
    const STANDARD_NAMES    = ['signature', 'date', 'time', 'charset', 'currency_code', 'currency_prefix', 'currency_suffix'];

    /**
     * Legacy templates found, with what copying them would do
     * @return array[] ['id' => 'ggs|<legacy name>', 'source', 'legacy', 'key', 'languages', 'same', 'replaces_edited', 'unknown', 'aliased']
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

                $catalog = EmailCatalog::find($key);
                $default = null;
                $aliased = [];
                $unknown = [];
                foreach($rows as $row)
                {
                    if((string)$row->language === '')
                    {
                        $default = $row;
                    }
                    foreach(self::scan($row->subject . ' ' . $row->message, $catalog) as $variable => $alias)
                    {
                        if($alias === null)
                        {
                            $unknown[$variable] = $variable;
                        }
                        else
                        {
                            $aliased[$variable] = $variable;
                        }
                    }
                }
                $current = Capsule::table(EmailManager::TABLE)->where('name', EmailManager::name($key))->where('language', '')->first();

                $found[] = [
                    'id'              => $source . '|' . $legacy,
                    'source'          => $source,
                    'legacy'          => $legacy,
                    'key'             => $key,
                    'languages'       => count($rows) - ($default ? 1 : 0),
                    'same'            => $default && $current && self::normalize(self::rewrite($default->message, $catalog)) === self::normalize($current->message) && self::normalize(self::rewrite($default->subject, $catalog)) === self::normalize($current->subject),
                    'replaces_edited' => $current && (self::normalize($current->message) !== self::normalize($catalog['message']) || self::normalize($current->subject) !== self::normalize($catalog['subject'])),
                    'unknown'         => array_values($unknown),
                    'aliased'         => array_values($aliased),
                ];
            }
        }

        return $found;
    }

    /**
     * What the migration copies on its own: every legacy text of the source whose fields the unified module fills (after
     * the alias rewrite), that is not copied already, does not replace a text the admin edited here, and belongs to an
     * e-mail this version sends. The first legacy template wins when two map to one.
     * @return array ['ids' => [...], 'skipped' => [['legacy', 'key', 'reason', 'fields']]]
     */
    public static function auto($source)
    {
        $ids     = [];
        $skipped = [];
        $taken   = [];

        foreach(self::candidates() as $candidate)
        {
            if($candidate['source'] !== $source)
            {
                continue;
            }
            $reason = null;
            if($candidate['same'])
            {
                continue;
            }
            if(isset($taken[$candidate['key']]))
            {
                $reason = 'another legacy template is copied for this e-mail';
            }
            elseif(!EmailCatalog::find($candidate['key'])['sent'])
            {
                $reason = 'this e-mail is not sent by the unified module';
            }
            elseif($candidate['replaces_edited'])
            {
                $reason = 'the unified e-mail was already edited';
            }
            elseif($candidate['unknown'])
            {
                $reason = 'uses fields the unified module does not fill';
            }

            if($reason)
            {
                $skipped[] = ['legacy' => $candidate['legacy'], 'key' => $candidate['key'], 'reason' => $reason, 'fields' => $candidate['unknown']];
                continue;
            }
            $taken[$candidate['key']] = true;
            $ids[]                    = $candidate['id'];
        }

        return ['ids' => $ids, 'skipped' => $skipped];
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

            $target  = EmailManager::name($candidate['key']);
            $catalog = EmailCatalog::find($candidate['key']);
            foreach(Capsule::table(EmailManager::TABLE)->where('name', $candidate['legacy'])->get() as $row)
            {
                $existing = Capsule::table(EmailManager::TABLE)->where('name', $target)->where('language', (string)$row->language)->first();
                $values   = [
                    'type'          => 'product', 'subject' => self::rewrite($row->subject, $catalog), 'message' => self::rewrite($row->message, $catalog), 'attachments' => (string)$row->attachments,
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
     * Variables used in the text that the unified module does not fill: [variable => alias or null]
     * (an alias means the legacy name is rewritten to the unified one on copy)
     */
    protected static function scan($text, array $catalog)
    {
        $found = [];
        preg_match_all('/\{[^{}]*\}/', (string)$text, $blocks);
        foreach($blocks[0] as $block)
        {
            preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $block, $variables);
            foreach($variables[1] as $variable)
            {
                if(self::known($variable, $catalog))
                {
                    continue;
                }
                $alias           = self::ALIASES[$variable] ?? null;
                $found[$variable] = $alias !== null && self::known($alias, $catalog) ? $alias : null;
            }
        }

        return $found;
    }

    protected static function known($variable, array $catalog)
    {
        if(in_array($variable, array_merge($catalog['fields'], EmailCatalog::STANDARD_FIELDS, self::STANDARD_NAMES), true))
        {
            return true;
        }
        foreach(self::STANDARD_PREFIXES as $prefix)
        {
            if(strpos($variable, $prefix) === 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * The text with the aliasable legacy fields renamed ({$order_link} -> {$order_url}); other fields stay as they are
     */
    protected static function rewrite($text, array $catalog)
    {
        $aliases = array_filter(self::scan($text, $catalog));
        if(!$aliases)
        {
            return (string)$text;
        }

        return preg_replace_callback('/\{[^{}]*\}/', function($block) use ($aliases) {
            return preg_replace_callback('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', function($variable) use ($aliases) {
                return isset($aliases[$variable[1]]) ? '$' . $aliases[$variable[1]] : $variable[0];
            }, $block[0]);
        }, (string)$text);
    }

    protected static function normalize($text)
    {
        return trim(preg_replace('/\s+/', ' ', (string)$text));
    }
}
