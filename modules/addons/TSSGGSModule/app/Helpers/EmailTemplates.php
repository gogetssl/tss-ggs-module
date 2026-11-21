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

    const CS_CONFIGURATION_TEMPLATE = 'Code Signing Configuration Required';
    const CS_CONFIGURATION_SUBJECT  = 'Code Signing Certificate - order details required';
    const CS_CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>
                                    Your code signing certificate ({$service_product_name}) is ready to be ordered.
                                    Enter your organization, the administrator contact and how you want to receive the
                                    certificate at the URL below.
                                 </p>
                                 <p>{$ssl_configuration_link}</p>
                                 <p>
                                    After you submit the order, the certificate authority validates your organization and
                                    contacts the administrator by email and phone. If you have any questions, please open a
                                    ticket for assistance.
                                 </p>
                                 <p>{$signature}</p>';

    const CS_ISSUED_TEMPLATE = 'Code Signing Certificate Issued';
    const CS_ISSUED_SUBJECT  = 'Your code signing certificate is issued - {$cs_organization}';
    const CS_ISSUED_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>Your code signing certificate for {$cs_organization} is issued. It is valid until {$cs_valid_till}.</p>
                                 <p>
                                    {if $cs_method == \'ship-token\'}The certificate authority ships the hardware token with the
                                    certificate to the shipping address of your order.{elseif $cs_method == \'existing-token\'}The
                                    certificate authority emails the administrator instructions to install the certificate on your
                                    hardware token.{else}The certificate authority emails the administrator instructions to download
                                    the certificate for your HSM.{/if}
                                 </p>
                                 <p>Order details: {$order_url}</p>
                                 <p>{$signature}</p>';

    const CS_EXPIRATION_TEMPLATE = 'Code Signing Expiration';
    const CS_EXPIRATION_SUBJECT  = 'Your code signing certificate expires in {$expireDaysLeft} days - {$domain}';
    const CS_EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p>
                                 <p>Your code signing certificate for {$domain} expires in {$expireDaysLeft} days ({$cert_expiry_date}).</p>
                                 <p>
                                    To keep signing your software, renew the service. After the renewal is paid you order the next
                                    certificate in the client area for your existing hardware token or HSM, and the certificate
                                    authority validates the organization again. Your current certificate stays valid until it expires.
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


    const RENEWAL_DETAILS_TEMPLATE = 'Renewal Details Required';
    const RENEWAL_DETAILS_SUBJECT  = 'SSL Certificate - renewal details required';
    const RENEWAL_DETAILS_MESSAGE  = '<p>Dear {$client_name},</p>
                                     <p>
                                        Thank you for renewing your SSL certificate for {$service_domain}.
                                        To order the renewed certificate we need its details once more - the CSR,
                                        the domains and the contacts. Please submit them at the URL below.
                                     </p>
                                     <p>{$ssl_configuration_link}</p>
                                     <p>
                                        Your current certificate stays valid and downloadable until it expires, and the
                                        days it has left are added to the renewed one.
                                     </p>
                                     <p>{$signature}</p>';


    /** keys of e-mails the module no longer has: their templates are deleted on upgrade (SchemaIndexes) */
    const REMOVED_TEMPLATES = [
        'Renewal', // the GoGetSSL module's "expires within 30 days, click Renew" - Service Expiration does it (removed in 1.9.0)
    ];

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
    const SUBSCRIPTION_EXPIRATION_MESSAGE  = '<p>Dear {$client_name},</p><p>A renewal invoice was generated for your subscription service #{$service_id}. Please pay the invoice before its due date to avoid automatic subscription cancellation.</p><p>{$signature}</p>';


    const SUBSCRIPTION_RENEWAL_TEMPLATE = 'ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_SUBJECT  = 'ACME Subscription Renewal';
    const SUBSCRIPTION_RENEWAL_MESSAGE  = '<p>Dear {$client_name},</p><p>We have received your payment for the renewal invoice of your subscription service #{$service_id}. Your subscription has been successfully renewed and remains active.</p><p>{$signature}</p>';


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

    const DS_CONFIGURATION_TEMPLATE = 'Document Signing Configuration Required';
    const DS_CONFIGURATION_SUBJECT  = 'Document Signing Certificate - order details required';
    const DS_CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your document signing certificate ({$service_product_name}) is ready to be ordered.
                                Enter your organization, the administrator contact and whether the certificate authority
                                should ship a token or you already have one, at the URL below.
                             </p>
                             <p>{$ssl_configuration_link}</p>
                             <p>
                                After you submit the order, the certificate authority validates your organization and
                                contacts the administrator by email and phone. If you have any questions, please open a
                                ticket for assistance.
                             </p>
                             <p>{$signature}</p>';

    const DS_ISSUED_TEMPLATE = 'Document Signing Certificate Issued';
    const DS_ISSUED_SUBJECT  = 'Your document signing certificate is issued - {$ds_organization}';
    const DS_ISSUED_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>Your document signing certificate for {$ds_organization} is issued. It is valid until {$ds_valid_till}.</p>
                             <p>
                                {if $ds_method == \'ship-token\'}The certificate authority ships the hardware token with the
                                certificate to the shipping address of your order.{else}The certificate authority emails the
                                administrator instructions to install the certificate on your token.{/if}
                             </p>
                             <p>Order details: {$order_url}</p>
                             <p>{$signature}</p>';

    const LEI_CONFIGURATION_TEMPLATE = 'LEI Configuration Required';
    const LEI_CONFIGURATION_SUBJECT  = 'LEI code - legal entity details required';
    const LEI_CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your LEI code ({$service_product_name}) is ready to be ordered. Enter the legal entity
                                (registered name, registration number, jurisdiction and addresses) and the person who signs
                                the application at the URL below.
                             </p>
                             <p>{$ssl_configuration_link}</p>
                             <p>
                                After you submit the order, the LEI issuer checks the data with the business register.
                                If it needs you to confirm what it found, it emails you. If you have any questions,
                                please open a ticket for assistance.
                             </p>
                             <p>{$signature}</p>';

    const LEI_ISSUED_TEMPLATE = 'LEI Issued';
    const LEI_ISSUED_SUBJECT  = 'Your LEI code - {$lei_legal_name}';
    const LEI_ISSUED_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                {if $lei_is_renewal}The LEI code of {$lei_legal_name} is renewed.{else}The LEI code of
                                {$lei_legal_name} is issued.{/if}
                             </p>
                             <p>LEI: <strong>{$lei_number}</strong><br>Registered until: {$lei_valid_till}</p>
                             <p>
                                The record is public in the global LEI index (GLEIF) and can be checked at
                                https://search.gleif.org/#/record/{$lei_number}
                             </p>
                             <p>Order details: {$order_url}</p>
                             <p>{$signature}</p>';

    /**
     * Sent when an OV / EV order (SSL, S/MIME, code signing, document signing) has been placed: the certificate
     * authority now verifies the organization and contacts the administrator - see App\Libs\OrganizationValidation
     */
    const ORG_VALIDATION_TEMPLATE = 'Organization Validation Required';
    const ORG_VALIDATION_SUBJECT  = 'Organization validation for your order - {$service_product_name}';
    const ORG_VALIDATION_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your order for {$service_product_name}{if $domain} ({$domain}){/if} has been placed.
                                {if $is_renewal}As this is a renewal, the certificate authority may reuse the organization
                                details it verified before; if they have to be confirmed again, it contacts you as described
                                below.{else}Before the certificate can be issued, the certificate authority verifies your
                                organization ({if $validation_type == \'EV\'}Extended Validation{else}Organization
                                Validation{/if}).{/if}
                             </p>
                             <p>What to expect:</p>
                             <ul>
                                <li>The certificate authority contacts the administrator contact of the order by email and by
                                phone, to confirm the organization details and that the order is authorized. Please answer
                                promptly: an unanswered call or email is the most common cause of delay.</li>
                                <li>It checks the organization in public business registers. If it cannot confirm the details
                                there, it may ask for documents, such as a business registration extract.</li>
                                <li>{if $validation_type == \'EV\'}Extended Validation usually takes a few working days{else}Organization
                                Validation usually takes 1-3 working days{/if} once everything the certificate authority asked
                                for has been confirmed.</li>
                             </ul>
                             <p>Order details: {$order_url}</p>
                             <p>{$signature}</p>';

    /** Sent when the certificate authority revoked the certificate: the service is cancelled (App\Libs\CertificateEnd) */
    const CERT_REVOKED_TEMPLATE = 'Certificate Revoked';
    const CERT_REVOKED_SUBJECT  = 'Important: your certificate has been revoked - {$service_product_name}';
    const CERT_REVOKED_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your certificate {$service_product_name}{if $domain} for <strong>{$domain}</strong>{/if}
                                {if $serial_number}(serial number {$serial_number}){/if} has been revoked by the certificate
                                authority. It is no longer trusted: browsers and other software show a warning or refuse it.
                             </p>
                             <p>
                                The service has been cancelled. Please contact our support team, so that we can find out why
                                it was revoked and arrange a replacement.
                             </p>
                             <p>Order details: {$order_url}</p>
                             <p>{$signature}</p>';

    /** Sent when the certificate expired and was not renewed: the service is terminated (App\Libs\CertificateEnd) */
    const CERT_EXPIRED_TEMPLATE = 'Certificate Expired';
    const CERT_EXPIRED_SUBJECT  = 'Your certificate has expired - {$service_product_name}';
    const CERT_EXPIRED_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your certificate {$service_product_name}{if $domain} for <strong>{$domain}</strong>{/if}
                                expired{if $cert_expiry} on {$cert_expiry}{/if} and was not renewed. Websites and software
                                that use it now show a security warning.
                             </p>
                             <p>
                                To restore the protection, order a new certificate in our client area: {$whmcs_url}cart.php
                             </p>
                             <p>{$signature}</p>';

    /**
     * Sent when a CodeGuard licence is set up. Nothing has to be configured - the client only needs the way in.
     */
    const CODE_GUARD_READY_TEMPLATE = 'CodeGuard Licence Ready';
    const CODE_GUARD_READY_SUBJECT  = 'Your CodeGuard licence is ready';
    const CODE_GUARD_READY_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your CodeGuard licence ({$service_product_name}) is active. Open your service in the client
                                area and use the button there to go to the CodeGuard dashboard, where you add the websites
                                and databases you want backed up.
                             </p>
                             <p>{$ssl_configuration_link}</p>
                             <p>{$signature}</p>';

    const PCI_CONFIGURATION_TEMPLATE = 'HackerGuardian PCI Configuration Required';
    const PCI_CONFIGURATION_SUBJECT  = 'HackerGuardian PCI scanning - organization details required';
    const PCI_CONFIGURATION_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                Your PCI scanning service ({$service_product_name}) is ready to be set up. Enter the
                                organization and the administrator of the scan account at the URL below.
                             </p>
                             <p>{$ssl_configuration_link}</p>
                             <p>
                                The scan account is created at once and its login is shown on the same page. If you have
                                any questions, please open a ticket for assistance.
                             </p>
                             <p>{$signature}</p>';

    const PCI_READY_TEMPLATE = 'HackerGuardian PCI Scan Account Ready';
    const PCI_READY_SUBJECT  = 'Your HackerGuardian PCI scan account is ready';
    const PCI_READY_MESSAGE  = '<p>Dear {$client_name},</p>
                             <p>
                                The PCI scan account of {$pci_organization} is ready. Sign in to the scan control centre
                                at {$pci_login_url} with the username <strong>{$pci_username}</strong>. The password is shown
                                on your service page in the client area.
                             </p>
                             <p>
                                In the scan control centre you add the IP addresses to scan, run the scans and get the
                                PCI compliance reports.
                             </p>
                             <p>Service details: {$order_url}</p>
                             <p>{$signature}</p>';

    const TEMPLATE_NAMES = ['CONFIGURATION', 'SMIME_CONFIGURATION', 'SMIME_REMINDER', 'SMIME_ISSUED', 'SMIME_EXPIRATION', 'CS_CONFIGURATION', 'CS_ISSUED', 'CS_EXPIRATION', 'EXPIRATION', 'SEND_CERTIFICATE', 'RENEWAL_DETAILS', 'REISSUE', 'SUBSCRIPTION', 'SUBSCRIPTION_EXPIRATION', 'SUBSCRIPTION_RENEWAL', 'RENEWAL_INVOICE', 'DISCONTINUED', 'CODE_GUARD_READY', 'DS_CONFIGURATION', 'DS_ISSUED', 'PCI_CONFIGURATION', 'PCI_READY', 'LEI_CONFIGURATION', 'LEI_ISSUED', 'ORG_VALIDATION', 'CERT_REVOKED', 'CERT_EXPIRED'];

    /** S/MIME e-mails */
    const SMIME_TEMPLATES = [self::SMIME_CONFIGURATION_TEMPLATE, self::SMIME_REMINDER_TEMPLATE, self::SMIME_ISSUED_TEMPLATE, self::SMIME_EXPIRATION_TEMPLATE];

    /** Code signing e-mails (GoGetSSL edition only) */
    const CS_TEMPLATES = [self::CS_CONFIGURATION_TEMPLATE, self::CS_ISSUED_TEMPLATE, self::CS_EXPIRATION_TEMPLATE];

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

        $currency = \getCurrency($invoice->userid);
        $systemUrl = rtrim((string)\WHMCS\Database\Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value'), '/');
        self::sendEmail(self::RENEWAL_INVOICE_TEMPLATE, (int)$serviceId, [
            'invoice_id'       => (int)$invoice->id,
            'invoice_total'    => (string)\formatCurrency($invoice->total, isset($currency['id']) ? $currency['id'] : false),
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
