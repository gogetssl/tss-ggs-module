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


    const SMIME_CONFIGURATION_TEMPLATE = 'S/MIME Configuration Required';
    const SMIME_CONFIGURATION_SUBJECT  = 'S/MIME Certificate - order details required';
    const SMIME_CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>
                                    Your S/MIME certificate ({$service_product_name}) is ready to be ordered.
                                    Enter the email address it should secure at the URL below.
                                 </p>
                                 <p>{$ssl_configuration_link}</p>
                                 <p>
                                    After you submit the order, the certificate authority emails that address
                                    to confirm you control the mailbox. If you have any questions,
                                    please open a ticket for assistance.
                                 </p>
                                 <p>{$signature}</p>';

    const SMIME_REMINDER_TEMPLATE = 'S/MIME Mailbox Not Confirmed';
    const SMIME_REMINDER_SUBJECT  = 'Confirm your mailbox to receive your S/MIME certificate';
    const SMIME_REMINDER_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>
                                    On {$smime_ordered_date} the certificate authority emailed {$smime_pending_emails}
                                    to confirm that you control the mailbox. The certificate can only be issued
                                    after this confirmation.
                                 </p>
                                 <p>
                                    Please open that email (check the spam folder too) and follow its instructions.
                                    You can see the status of your order here: {$order_url}
                                 </p>
                                 <p>{$signature}</p>';

    const SMIME_ISSUED_TEMPLATE = 'S/MIME Certificate Issued';
    const SMIME_ISSUED_SUBJECT  = 'Your S/MIME certificate is issued - {$smime_emails}';
    const SMIME_ISSUED_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>Your S/MIME certificate for {$smime_emails} is issued. It is valid until {$smime_valid_till}.</p>
                                 <p>
                                    {if $smime_csr_provided}The certificate is issued for the CSR you sent: install it in your email
                                    client together with the private key you created.{else}The certificate authority emails you how to
                                    collect the certificate and its private key. Keep the private key safe: without it you can not read
                                    the emails encrypted for this certificate.{/if}
                                 </p>
                                 <p>Order details: {$order_url}</p>
                                 <p>{$signature}</p>';

    const SMIME_EXPIRATION_TEMPLATE = 'S/MIME Expiration';
    const SMIME_EXPIRATION_SUBJECT  = 'Your S/MIME certificate expires in {$expireDaysLeft} days - {$domain}';
    const SMIME_EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>Your S/MIME certificate for {$domain} expires in {$expireDaysLeft} days ({$cert_expiry_date}).</p>
                                 <p>
                                    To keep signing and encrypting your email, renew the service. After the renewal is paid you
                                    order the next certificate in the client area, and the certificate authority confirms the
                                    mailbox again. Your current certificate stays valid until it expires.
                                 </p>
                                 <p>{$order_url}</p>
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


    /**
     * Replaces WHMCS's own "Invoice Created" e-mail for the renewal invoices this module creates - only when the admin
     * enables it (it is created disabled, so nothing changes for existing installs)
     */
    const RENEWAL_INVOICE_TEMPLATE = 'Renewal Invoice Created';
    const RENEWAL_INVOICE_SUBJECT  = 'Renewal invoice #{$invoice_id} - {$service_domain}';
    const RENEWAL_INVOICE_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                A renewal invoice was created for your SSL certificate {$service_domain} (service #{$service_id}).
                                Amount: <strong>{$invoice_total}</strong>, due {$invoice_due_date}.
                             </p>
                             <p><a href="{$invoice_url}">View and pay the invoice</a></p>
                             <p>{$signature}</p>';

    /**
     * Sent with the renewal invoice of a service whose product the gateway no longer sells (retired / removed)
     */
    const DISCONTINUED_TEMPLATE = 'Product Discontinued';
    const DISCONTINUED_SUBJECT  = 'Your certificate product {$service_product_name} is discontinued';
    const DISCONTINUED_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                The product of your SSL certificate {$service_domain} (service #{$service_id}) is no longer offered.
                                Renewal may not be possible under the same product - please open a ticket and we will arrange a
                                replacement certificate before your current one expires.
                             </p>
                             <p>{$signature}</p>';

    const TEMPLATE_NAMES = ['CONFIGURATION', 'SMIME_CONFIGURATION', 'SMIME_REMINDER', 'SMIME_ISSUED', 'SMIME_EXPIRATION', 'EXPIRATION', 'SEND_CERTIFICATE', 'RENEWAL', 'REISSUE', 'SUBSCRIPTION', 'SUBSCRIPTION_EXPIRATION', 'SUBSCRIPTION_RENEWAL', 'RENEWAL_INVOICE', 'DISCONTINUED'];

    /** S/MIME e-mails (GoGetSSL edition only) */
    const SMIME_TEMPLATES = [self::SMIME_CONFIGURATION_TEMPLATE, self::SMIME_REMINDER_TEMPLATE, self::SMIME_ISSUED_TEMPLATE, self::SMIME_EXPIRATION_TEMPLATE];

    /** keys of the templates that are created disabled */
    const OPT_IN = [self::RENEWAL_INVOICE_TEMPLATE];

    public static function createTemplates()
    {
        foreach(self::TEMPLATE_NAMES as $name)
        {
            $key = constant('self::' . $name . '_TEMPLATE');
            self::resolveTemplate($key, constant('self::' . $name . '_SUBJECT'), constant('self::' . $name . '_MESSAGE'), in_array($key, self::OPT_IN, true));
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

    public static function renewalInvoiceEmailActive()
    {
        $row = EmailTemplate::where('name', Edition::templateName(self::RENEWAL_INVOICE_TEMPLATE))->where('language', '')->first();

        return $row && (int)$row->disabled === 0;
    }

    /**
     * The module's renewal invoice e-mail; false when it is disabled (the caller then lets WHMCS send its own invoice e-mail)
     */
    public static function sendRenewalInvoiceEmail($serviceId, $invoiceId)
    {
        $invoice = \WHMCS\Database\Capsule::table('tblinvoices')->where('id', (int)$invoiceId)->first(['id', 'userid', 'total', 'duedate']);
        if(!$invoice || !self::renewalInvoiceEmailActive())
        {
            return false;
        }

        \getCurrency($invoice->userid);
        $systemUrl = rtrim((string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        self::sendEmail(self::RENEWAL_INVOICE_TEMPLATE, (int)$serviceId, [
            'invoice_id'       => (int)$invoice->id,
            'invoice_total'    => (string)\formatCurrency($invoice->total),
            'invoice_due_date' => (string)$invoice->duedate,
            'invoice_url'      => $systemUrl . '/viewinvoice.php?id=' . (int)$invoice->id,
        ]);

        return true;
    }

    /**
     * Returns the WHMCS name of the template for this edition ("<edition name> - <key>"). A template created under
     * another edition's name or the 1.2.x name is renamed (keeps the admin's edits); a missing one is created when
     * $subject/$message are given.
     */
    public static function resolveTemplate($key, $subject = null, $message = null, $disabled = false)
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
                                      'disabled'   => $disabled ? '1' : '0',
                                      'custom'     => '1',
                                      'plaintext'  => '0',
                                      'created_at' => date('Y-m-d H:i:s'),
                                      'updated_at' => date('Y-m-d H:i:s')
                                  ]);
        }

        return $name;
    }

}
