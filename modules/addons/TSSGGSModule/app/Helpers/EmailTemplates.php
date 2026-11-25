<?php

namespace TSSGGSModule\App\Helpers;

use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Models\Whmcs\EmailTemplate;

class EmailTemplates
{
    const CONFIGURATION_TEMPLATE = 'Configuration Required';

    const SMIME_CONFIGURATION_TEMPLATE = 'S/MIME Configuration Required';

    const SMIME_REMINDER_TEMPLATE = 'S/MIME Mailbox Not Confirmed';

    const SMIME_ISSUED_TEMPLATE = 'S/MIME Certificate Issued';

    const SMIME_EXPIRATION_TEMPLATE = 'S/MIME Expiration';

    const CS_CONFIGURATION_TEMPLATE = 'Code Signing Configuration Required';

    const CS_ISSUED_TEMPLATE = 'Code Signing Certificate Issued';

    const CS_EXPIRATION_TEMPLATE = 'Code Signing Expiration';

    const EXPIRATION_TEMPLATE = 'Service Expiration';

    const SEND_CERTIFICATE_TEMPLATE = 'Send Certificate';

    const RENEWAL_DETAILS_TEMPLATE = 'Renewal Details Required';

    /** keys of e-mails the module no longer has: their templates are deleted on upgrade (App\Database\Schema::afterUpgrade) */
    const REMOVED_TEMPLATES = [
        'Renewal', // the GoGetSSL module's "expires within 30 days, click Renew" - Service Expiration does it (removed in 1.9.0)
    ];

    const REISSUE_TEMPLATE = 'Reissue';

    const SUBSCRIPTION_TEMPLATE = 'ACME Subscription Configuration';

    const SUBSCRIPTION_EXPIRATION_TEMPLATE = 'ACME Subscription Expiration';

    const SUBSCRIPTION_RENEWAL_TEMPLATE = 'ACME Subscription Renewal';

    /**
     * Replaces WHMCS's own "Invoice Created" e-mail for the renewal invoices this module creates - only when the admin
     * enables it (it is created disabled, so nothing changes for existing installs)
     */
    const RENEWAL_INVOICE_TEMPLATE = 'Renewal Invoice Created';

    /**
     * Sent with the renewal invoice of a service whose product the gateway no longer sells (retired / removed)
     */
    const DISCONTINUED_TEMPLATE = 'Product Discontinued';

    const DS_CONFIGURATION_TEMPLATE = 'Document Signing Configuration Required';

    const DS_ISSUED_TEMPLATE = 'Document Signing Certificate Issued';

    const LEI_CONFIGURATION_TEMPLATE = 'LEI Configuration Required';

    const LEI_ISSUED_TEMPLATE = 'LEI Issued';

    /**
     * Sent when an OV / EV order (SSL, S/MIME, code signing, document signing) has been placed: the certificate
     * authority now verifies the organization and contacts the administrator - see App\Libs\Certificates\OrganizationValidation
     */
    const ORG_VALIDATION_TEMPLATE = 'Organization Validation Required';

    /** Sent when the certificate authority revoked the certificate: the service is cancelled (App\Libs\Certificates\CertificateEnd) */
    const CERT_REVOKED_TEMPLATE = 'Certificate Revoked';

    /** Sent when the certificate expired and was not renewed: the service is terminated (App\Libs\Certificates\CertificateEnd) */
    const CERT_EXPIRED_TEMPLATE = 'Certificate Expired';

    /**
     * Sent when a CodeGuard licence is set up. Nothing has to be configured - the client only needs the way in.
     */
    const CODE_GUARD_READY_TEMPLATE = 'CodeGuard Licence Ready';

    const PCI_CONFIGURATION_TEMPLATE = 'HackerGuardian PCI Configuration Required';

    const PCI_READY_TEMPLATE = 'HackerGuardian PCI Scan Account Ready';

    const TEMPLATE_NAMES = ['CONFIGURATION', 'SMIME_CONFIGURATION', 'SMIME_REMINDER', 'SMIME_ISSUED', 'SMIME_EXPIRATION', 'CS_CONFIGURATION', 'CS_ISSUED', 'CS_EXPIRATION', 'EXPIRATION', 'SEND_CERTIFICATE', 'RENEWAL_DETAILS', 'REISSUE', 'SUBSCRIPTION', 'SUBSCRIPTION_EXPIRATION', 'SUBSCRIPTION_RENEWAL', 'RENEWAL_INVOICE', 'DISCONTINUED', 'CODE_GUARD_READY', 'DS_CONFIGURATION', 'DS_ISSUED', 'PCI_CONFIGURATION', 'PCI_READY', 'LEI_CONFIGURATION', 'LEI_ISSUED', 'ORG_VALIDATION', 'CERT_REVOKED', 'CERT_EXPIRED'];

    /** S/MIME e-mails */
    const SMIME_TEMPLATES = [self::SMIME_CONFIGURATION_TEMPLATE, self::SMIME_REMINDER_TEMPLATE, self::SMIME_ISSUED_TEMPLATE, self::SMIME_EXPIRATION_TEMPLATE];

    /** Code signing e-mails (GoGetSSL edition only) */
    const CS_TEMPLATES = [self::CS_CONFIGURATION_TEMPLATE, self::CS_ISSUED_TEMPLATE, self::CS_EXPIRATION_TEMPLATE];

    /** keys of the templates that are created disabled */
    const OPT_IN = [self::RENEWAL_INVOICE_TEMPLATE];

    /** the default texts of the e-mails: one file per e-mail, the subject in its first line (see defaultsFromFile) */
    const DEFAULTS_DIR = 'resources/emails';

    protected static $defaults = [];

    /**
     * [subject, message] the module ships for an e-mail: resources/emails/<file>.html, first line
     * "<!-- subject: ... -->", the HTML message below. Translators and copywriters edit those files, not PHP.
     */
    public static function defaultsFromFile(string $file): array
    {
        if(!isset(self::$defaults[$file]))
        {
            $path = dirname(__DIR__, 2) . '/' . self::DEFAULTS_DIR . '/' . $file . '.html';
            $text = is_file($path) ? (string)file_get_contents($path) : '';
            if(!preg_match('/\A<!-- subject: (.*?) -->\r?\n(.*)\z/s', $text, $m))
            {
                throw new \RuntimeException('E-mail default missing or without a subject line: ' . self::DEFAULTS_DIR . '/' . $file . '.html');
            }
            self::$defaults[$file] = [$m[1], rtrim($m[2])];
        }

        return self::$defaults[$file];
    }

    /** [subject, message] of one of TEMPLATE_NAMES, e.g. "SMIME_ISSUED" -> resources/emails/smime-issued.html */
    public static function defaults(string $name): array
    {
        return self::defaultsFromFile(strtolower(str_replace('_', '-', $name)));
    }

    public static function createTemplates()
    {
        foreach(self::TEMPLATE_NAMES as $name)
        {
            $key = constant('self::' . $name . '_TEMPLATE');
            [$subject, $message] = self::defaults($name);
            self::resolveTemplate($key, $subject, $message, in_array($key, self::OPT_IN, true));
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
                [$subject, $message] = self::defaults($name);
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
