<?php

namespace ModulesGarden\TSSGGSModule\App\Helpers;

use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\EmailTemplate;

class EmailTemplates
{
    const CONFIGURATION_TEMPLATE = 'The SSL Store & GoGetSSL Module - Configuration Required';
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


    const EXPIRATION_TEMPLATE = 'The SSL Store & GoGetSSL Module - Service Expiration';
    const EXPIRATION_SUBJECT  = 'Service Expiration Notification - {$service_domain}';
    const EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                <p>We would like to inform You about your service <strong>#{$service_id}</strong> is going to expire in {$expireDaysLeft} days.</p>
                                <p>{$signature}</p>';


    const SEND_CERTIFICATE_TEMPLATE = 'The SSL Store & GoGetSSL Module - Send Certificate';
    const SEND_CERTIFICATE_SUBJECT  = 'SSL Certificate';
    const SEND_CERTIFICATE_MESSAGE  = '<p>Dear {$client_name},</p>
                                      <p>Domain: </p>
                                      <p>{$domain}</p>
                                      <p>Intermediate certificate: </p>
                                      <p>{$ssl_certyficate}</p>
                                      <p>CRT: </p>
                                      <p>{$crt_code}</p>
                                      <p>{$signature}</p>';


    const RENEWAL_TEMPLATE = 'The SSL Store & GoGetSSL Module - Renewal';
    const RENEWAL_SUBJECT  = 'SSL Certificate - Renew';
    const RENEWAL_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your current SSL certificate #{$service_id} expires within next 30-days. 
                                Please login to the client area and click "Renew" button to start the renewal process.
                             </p>
                             <p>{$signature}</p>';


    const REISSUE_TEMPLATE = 'The SSL Store & GoGetSSL Module - Reissue';
    const REISSUE_SUBJECT  = 'SSL Certificate - Reissue';
    const REISSUE_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your current SSL certificate #{$service_id} expires within next 30-days. 
                                You are using an SSL subscription and no other payments are required. 
                                However, you need to reissue your SSL certificate to receive new files for the next period.
                             </p>
                             <p>{$signature}</p>';

    const SUBSCRIPTION_TEMPLATE = 'The SSL Store & GoGetSSL Module - ACME Subscription Configuration';
    const SUBSCRIPTION_SUBJECT  = 'ACME Subscription - configuration required';
    const SUBSCRIPTION_MESSAGE  = '<p>Dear {$client_name},</p>
                                  <p>Your ACME subscription service #{$service_id} is ready for configuration in client area. Configuration can be done at the URL below.</p>
                                  <p>{$ssl_configuration_link}</p>
                                  <p>Please add your domains and create subscription.</p>
                                  <p>{$signature}</p>';


    const SUBSCRIPTION_EXPIRATION_TEMPLATE = 'The SSL Store & GoGetSSL Module - ACME Subscription Expiration';
    const SUBSCRIPTION_EXPIRATION_SUBJECT  = 'ACME Subscription Expiration - {$service_domain}';
    const SUBSCRIPTION_EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p><p>A renewal invoice was generated for your ACME subscription service #{$service_id}. Please pay invoice to avoid automatic subscription cancellation.</p><p>{$signature}</p>';


    const SUBSCRIPTION_RENEWAL_TEMPLATE = 'The SSL Store & GoGetSSL Module - ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_SUBJECT  = 'ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_MESSAGE  = '<p>Dear {$client_name},</p><p>We have received your payment for the renewal invoice of your ACME subscription service #{$service_id}. Your subscription has been successfully renewed and remains active.</p><p>{$signature}</p>';


    public static function createTemplates()
    {
        foreach(['CONFIGURATION', 'EXPIRATION', 'SEND_CERTIFICATE', 'RENEWAL', 'REISSUE', 'SUBSCRIPTION', 'SUBSCRIPTION_EXPIRATION', 'SUBSCRIPTION_RENEWAL'] as $name)
        {
            $nameTemplate = constant('self::' . $name . '_TEMPLATE');
            $subject      = constant('self::' . $name . '_SUBJECT');
            $message      = constant('self::' . $name . '_MESSAGE');

            $check = EmailTemplate::where('name', $nameTemplate)->first();
            if($check->id)
            {
                continue;
            }

            EmailTemplate::insert([
                                      'type'       => 'product',
                                      'name'       => $nameTemplate,
                                      'subject'    => $subject,
                                      'message'    => $message,
                                      'disabled'   => '0',
                                      'custom'     => '1',
                                      'plaintext'  => '0',
                                      'created_at' => date('Y-m-d H:i:s'),
                                      'updated_at' => date('Y-m-d H:i:s')
                                  ]);

        }
    }

    public static function sendEmail($template, $id, $fields = [], $attachments = [])
    {
        \sendMessage($template, $id, $fields, false, $attachments);
    }

}
