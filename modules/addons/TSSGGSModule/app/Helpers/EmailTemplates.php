<?php

namespace ModulesGarden\TSSGGSModule\App\Helpers;

use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\EmailTemplate;

class EmailTemplates
{
    const CONFIGURATION_TEMPLATE = 'Configuration Required';
    const CONFIGURATION_SUBJECT  = 'SSL Certificate - configuration required';
    const CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>
                                    Thank you for your order for an SSL Certificate. 
                                    Before you can use your certificate, it requires 
                                    configuration which can be done at the URL below.
                                 </p>
                                 <p>{$ssl_configuration_link}</p>
                                 <p>
                                    Instructions are provided throughout the process 
                                    but if you experience any problems or have any questions, 
                                    please open a ticket for assistance.
                                 </p>
                                 <p>{$signature}</p>';


    const EXPIRATION_TEMPLATE = 'Service Expiration';
    const EXPIRATION_SUBJECT  = 'Service Expiration Notification - {$service_domain}';
    const EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                <p>We would like to inform You about your service <strong>#{$service_id}</strong> is going to expire in {$expireDaysLeft} days.</p>
                                <p>{$signature}</p>';


    const SEND_CERTIFICATE_TEMPLATE = 'Send Certificate';
    const SEND_CERTIFICATE_SUBJECT  = 'SSL Certificate';
    const SEND_CERTIFICATE_MESSAGE  = '<p>Dear {$client_name},</p>
                                      <p>Domain: </p>
                                      <p>{$domain}</p>
                                      <p>Intermediate certificate: </p>
                                      <p>{$ssl_certyficate}</p>
                                      <p>CRT: </p>
                                      <p>{$crt_code}</p>
                                      <p>{$signature}</p>';


    const RENEWAL_TEMPLATE = 'Renewal';
    const RENEWAL_SUBJECT  = 'SSL Certificate - Renew';
    const RENEWAL_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your current SSL certificate #{$service_id} expires within next 30-days. 
                                Please login to the client area and click "Renew" button to start the renewal process.
                             </p>
                             <p>{$signature}</p>';


    const REISSUE_TEMPLATE = 'Reissue';
    const REISSUE_SUBJECT  = 'SSL Certificate - Reissue';
    const REISSUE_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your current SSL certificate #{$service_id} expires within next 30-days. 
                                You are using an SSL subscription and no other payments are required. 
                                However, you need to reissue your SSL certificate to receive new files for the next period.
                             </p>
                             <p>{$signature}</p>';

    const SUBSCRIPTION_TEMPLATE = 'ACME Subscription Configuration';
    const SUBSCRIPTION_SUBJECT  = 'ACME Subscription - configuration required';
    const SUBSCRIPTION_MESSAGE  = '<p>Dear {$client_name},</p>
                                  <p>Your ACME subscription service #{$service_id} is ready for configuration in client area. Configuration can be done at the URL below.</p>
                                  <p>{$ssl_configuration_link}</p>
                                  <p>Please add your domains and create subscription.</p>
                                  <p>{$signature}</p>';


    const SUBSCRIPTION_EXPIRATION_TEMPLATE = 'ACME Subscription Expiration';
    const SUBSCRIPTION_EXPIRATION_SUBJECT  = 'ACME Subscription Expiration - {$service_domain}';
    const SUBSCRIPTION_EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p><p>A renewal invoice was generated for your ACME subscription service #{$service_id}. Please pay invoice to avoid automatic subscription cancellation.</p><p>{$signature}</p>';


    const SUBSCRIPTION_RENEWAL_TEMPLATE = 'ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_SUBJECT  = 'ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_MESSAGE  = '<p>Dear {$client_name},</p><p>We have received your payment for the renewal invoice of your ACME subscription service #{$service_id}. Your subscription has been successfully renewed and remains active.</p><p>{$signature}</p>';


    const TEMPLATE_NAMES = ['CONFIGURATION', 'EXPIRATION', 'SEND_CERTIFICATE', 'RENEWAL', 'REISSUE', 'SUBSCRIPTION', 'SUBSCRIPTION_EXPIRATION', 'SUBSCRIPTION_RENEWAL'];

    public static function createTemplates()
    {
        foreach(self::TEMPLATE_NAMES as $name)
        {
            self::resolveTemplate(
                constant('self::' . $name . '_TEMPLATE'),
                constant('self::' . $name . '_SUBJECT'),
                constant('self::' . $name . '_MESSAGE')
            );
        }

        AutoInstallEmailTemplates::createTemplates();
    }

    /**
     * $template is a template key (e.g. self::REISSUE_TEMPLATE); the WHMCS template name depends on the edition
     */
    public static function sendEmail($template, $id, $fields = [], $attachments = [])
    {
        $subject = $message = null;
        foreach(self::TEMPLATE_NAMES as $name)
        {
            if(constant('self::' . $name . '_TEMPLATE') === $template)
            {
                $subject = constant('self::' . $name . '_SUBJECT');
                $message = constant('self::' . $name . '_MESSAGE');
                break;
            }
        }

        \sendMessage(self::resolveTemplate($template, $subject, $message), $id, $fields + self::commonFields($id), false, $attachments);
    }

    /**
     * Merge fields every module e-mail can use (the ones a template gets explicitly win): the service domain, its client
     * area page, the order id, the product, and the expiry / next due date with the days left
     */
    public static function commonFields($serviceId)
    {
        $service   = \WHMCS\Database\Capsule::table('tblhosting')->where('id', (int)$serviceId)->first(['domain', 'nextduedate', 'packageid']);
        $systemUrl = rtrim((string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        $due       = $service && $service->nextduedate && $service->nextduedate !== '0000-00-00' ? (string)$service->nextduedate : '';

        return [
            'domain'                => $service ? (string)$service->domain : '',
            'order_url'             => $systemUrl . '/clientarea.php?action=productdetails&id=' . (int)$serviceId,
            'storeorder_id'         => (string)\WHMCS\Database\Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->value('remoteid'),
            'product_name'          => $service ? (string)\WHMCS\Database\Capsule::table('tblproducts')->where('id', $service->packageid)->value('name') : '',
            'cert_expiry_date'      => $due,
            'subscription_end_date' => $due,
            'expireDaysLeft'        => $due !== '' ? (int)floor((strtotime($due) - strtotime(date('Y-m-d'))) / 86400) : '',
        ];
    }

    /**
     * Returns the WHMCS name of the template for this edition ("<edition name> - <key>"). A template created under
     * another edition's name or the 1.2.x name is renamed (keeps the admin's edits); a missing one is created when
     * $subject/$message are given.
     */
    public static function resolveTemplate($key, $subject = null, $message = null)
    {
        $name = Edition::templateName($key);

        if(EmailTemplate::where('name', $name)->first())
        {
            return $name;
        }

        foreach(Edition::knownNames() as $knownName)
        {
            $otherName = $knownName . ' - ' . $key;
            if($otherName === $name)
            {
                continue;
            }

            if(EmailTemplate::where('name', $otherName)->update(['name' => $name, 'updated_at' => date('Y-m-d H:i:s')]))
            {
                return $name;
            }
        }

        if($subject !== null && $message !== null)
        {
            EmailTemplate::insert([
                                      'type'       => 'product',
                                      'name'       => $name,
                                      'subject'    => $subject,
                                      'message'    => $message,
                                      'disabled'   => '0',
                                      'custom'     => '1',
                                      'plaintext'  => '0',
                                      'created_at' => date('Y-m-d H:i:s'),
                                      'updated_at' => date('Y-m-d H:i:s')
                                  ]);
        }

        return $name;
    }

}
