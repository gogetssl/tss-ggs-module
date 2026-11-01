<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;

/**
 * The client e-mails the module sends: key (WHMCS template name suffix), group, merge fields the code passes,
 * default subject and text. Titles and "when sent" texts are in the language file (admin.emails.name_<slug>, when_<slug>).
 */
class EmailCatalog
{
    const GROUPS = ['order', 'validation', 'issued', 'renewals', 'problems'];

    /** key => [group, extra merge fields, sent by this version] */
    const META = [
        'Configuration Required'            => ['order', ['ssl_configuration_link'], true],
        'ACME Subscription Configuration'   => ['order', ['ssl_configuration_link'], true],
        'DNS Validation Switched to Email'  => ['validation', ['domain', 'dcv_email', 'order_url'], true],
        'Validation Warning'                => ['validation', ['domain', 'dcv_method', 'order_url'], true],
        'Send Certificate'                  => ['issued', ['domain', 'ssl_certyficate', 'crt_code'], true],
        'Certificate Installed'             => ['issued', ['domain', 'order_url'], true],
        'Service Expiration'                => ['renewals', ['expireDaysLeft'], true],
        'Reissue'                           => ['renewals', [], true],
        'Renewal Started'                   => ['renewals', ['domain', 'cert_expiry', 'days_left', 'order_url'], true],
        'Renewal Complete'                  => ['renewals', ['domain', 'order_url'], true],
        'Certificate Autorenewed'           => ['renewals', ['domain', 'order_number', 'common_name', 'order_url'], true],
        'ACME Subscription Expiration'      => ['renewals', [], true],
        'ACME Subscription Renewal'         => ['renewals', [], true],
        'Renewal'                           => ['renewals', [], false],
        'Auto Reissue Failed'               => ['problems', ['domain', 'cert_expiry', 'days_left', 'error_message', 'order_url'], true],
    ];

    /**
     * Merge fields WHMCS fills for every service e-mail (shown in the editor next to the template's own fields)
     */
    const STANDARD_FIELDS = ['client_name', 'client_first_name', 'client_last_name', 'client_email', 'service_product_name', 'service_domain', 'service_id', 'signature', 'company_name', 'whmcs_url'];

    protected static $all;

    /**
     * @return array key => ['key', 'slug', 'group', 'fields', 'sent', 'subject', 'message']
     */
    public static function all()
    {
        if(self::$all !== null)
        {
            return self::$all;
        }

        $defaults = [];
        foreach(EmailTemplates::TEMPLATE_NAMES as $name)
        {
            $defaults[constant(EmailTemplates::class . '::' . $name . '_TEMPLATE')] = [
                constant(EmailTemplates::class . '::' . $name . '_SUBJECT'),
                constant(EmailTemplates::class . '::' . $name . '_MESSAGE'),
            ];
        }
        foreach(AutoInstallEmailTemplates::definitions() as $key => $definition)
        {
            $defaults[$key] = $definition;
        }

        self::$all = [];
        foreach(self::META as $key => list($group, $fields, $sent))
        {
            if(!isset($defaults[$key]))
            {
                continue;
            }
            self::$all[$key] = [
                'key'     => $key,
                'slug'    => self::slug($key),
                'group'   => $group,
                'fields'  => $fields,
                'sent'    => $sent,
                'subject' => $defaults[$key][0],
                'message' => $defaults[$key][1],
            ];
        }

        return self::$all;
    }

    public static function slug($key)
    {
        return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '_', $key)), '_');
    }

    public static function find($key)
    {
        $all = self::all();

        return isset($all[$key]) ? $all[$key] : null;
    }

    /**
     * Sample values for the extra merge fields (preview, test e-mail)
     */
    public static function sample($key, $serviceId)
    {
        $systemUrl = rtrim((string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        $orderUrl  = $systemUrl . '/clientarea.php?action=productdetails&id=' . (int)$serviceId;

        return [
            'domain'                 => 'example.com',
            'common_name'            => 'www.example.com',
            'order_url'              => $orderUrl,
            'order_number'           => '123456',
            'cert_expiry'            => date('Y-m-d', strtotime('+20 days')),
            'days_left'              => 20,
            'expireDaysLeft'         => 15,
            'error_message'          => 'The CA rejected the request (sample error)',
            'dcv_email'              => 'admin@example.com',
            'dcv_method'             => 'HTTP',
            'ssl_configuration_link' => $orderUrl . '&configssl=1',
            'ssl_certyficate'        => '-----BEGIN CERTIFICATE-----<br>(CA bundle)<br>-----END CERTIFICATE-----',
            'crt_code'               => '-----BEGIN CERTIFICATE-----<br>(certificate)<br>-----END CERTIFICATE-----',
        ];
    }
}
