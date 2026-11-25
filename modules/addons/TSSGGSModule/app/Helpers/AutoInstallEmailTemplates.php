<?php

namespace TSSGGSModule\App\Helpers;

use WHMCS\Database\Capsule;

/**
 * Client notifications of the SSL AutoInstall (cPanel) feature.
 * Templates are created on module activation and, if missing, right before sending.
 */
class AutoInstallEmailTemplates
{
    const CERT_INSTALLED_TEMPLATE     = 'Certificate Installed';
    const RENEWAL_COMPLETE_TEMPLATE   = 'Renewal Complete';
    const DNS_TO_EMAIL_TEMPLATE       = 'DNS Validation Switched to Email';
    const AUTO_REISSUE_FAILED_TEMPLATE = 'Auto Reissue Failed';
    const VALIDATION_WARNING_TEMPLATE = 'Validation Warning';
    const RENEWAL_STARTED_TEMPLATE    = 'Renewal Started';
    const RENEWAL_AUTORENEWED_TEMPLATE = 'Certificate Autorenewed';

    /**
     * key => [subject, message] of the AutoInstall e-mails (the Emails page resets a template to these)
     */
    public static function definitions()
    {
        return self::getDefinitions();
    }

    /** template key => file in resources/emails (subject in its first line, see EmailTemplates::defaultsFromFile) */
    const FILES = [
        self::CERT_INSTALLED_TEMPLATE      => 'autoinstall-certificate-installed',
        self::RENEWAL_COMPLETE_TEMPLATE    => 'autoinstall-renewal-complete',
        self::DNS_TO_EMAIL_TEMPLATE        => 'autoinstall-dns-validation-switched-to-email',
        self::AUTO_REISSUE_FAILED_TEMPLATE => 'autoinstall-auto-reissue-failed',
        self::VALIDATION_WARNING_TEMPLATE  => 'autoinstall-validation-warning',
        self::RENEWAL_STARTED_TEMPLATE     => 'autoinstall-renewal-started',
        self::RENEWAL_AUTORENEWED_TEMPLATE => 'autoinstall-certificate-autorenewed',
    ];

    protected static function getDefinitions()
    {
        $definitions = [];
        foreach(self::FILES as $key => $file)
        {
            $definitions[$key] = EmailTemplates::defaultsFromFile($file);
        }

        return $definitions;
    }

    public static function createTemplates()
    {
        foreach(self::getDefinitions() as $name => $definition)
        {
            self::createTemplate($name, $definition[0], $definition[1]);
        }
    }

    /**
     * Returns the WHMCS template name for this edition, see EmailTemplates::resolveTemplate()
     */
    protected static function createTemplate($key, $subject, $message)
    {
        return EmailTemplates::resolveTemplate($key, $subject, $message);
    }

    protected static function send($template, $serviceId, array $vars)
    {
        $definitions = self::getDefinitions();
        $template    = isset($definitions[$template])
            ? self::createTemplate($template, $definitions[$template][0], $definitions[$template][1])
            : EmailTemplates::resolveTemplate($template);

        $systemUrl         = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
        $vars['order_url'] = rtrim((string)$systemUrl, '/') . '/clientarea.php?action=productdetails&id=' . (int)$serviceId;

        $result = localAPI('SendEmail', [
            'messagename' => $template,
            'id'          => (int)$serviceId,
            'customvars'  => base64_encode(serialize($vars)),
        ]);

        return isset($result['result']) && $result['result'] === 'success';
    }

    public static function sendCertInstalledEmail($serviceId, $domain)
    {
        return self::send(self::CERT_INSTALLED_TEMPLATE, $serviceId, ['domain' => $domain]);
    }

    public static function sendRenewalCompleteEmail($serviceId, $domain)
    {
        return self::send(self::RENEWAL_COMPLETE_TEMPLATE, $serviceId, ['domain' => $domain]);
    }

    public static function sendDnsToEmailSwitchEmail($serviceId, $domain, $dcvEmail)
    {
        return self::send(self::DNS_TO_EMAIL_TEMPLATE, $serviceId, [
            'domain'    => $domain,
            'dcv_email' => $dcvEmail ?: '(see your domain registrar contact)',
        ]);
    }

    public static function sendAutoReissueFailedEmail($serviceId, $domain, $certExpiry, $daysLeft, $errorMessage)
    {
        return self::send(self::AUTO_REISSUE_FAILED_TEMPLATE, $serviceId, [
            'domain'        => $domain,
            'cert_expiry'   => $certExpiry,
            'days_left'     => max(0, (int)$daysLeft),
            'error_message' => $errorMessage,
        ]);
    }

    public static function sendRenewalStartedEmail($serviceId, $domain, $certExpiry, $daysLeft)
    {
        return self::send(self::RENEWAL_STARTED_TEMPLATE, $serviceId, [
            'domain'      => $domain,
            'cert_expiry' => $certExpiry,
            'days_left'   => max(0, (int)$daysLeft),
        ]);
    }

    public static function sendRenewalAutorenewedEmail($serviceId, $domain, $orderNumber, $commonName)
    {
        return self::send(self::RENEWAL_AUTORENEWED_TEMPLATE, $serviceId, [
            'domain'       => $domain,
            'order_number' => $orderNumber,
            'common_name'  => $commonName ?: $domain,
        ]);
    }

    public static function sendValidationWarningEmail($serviceId, $domain, $dcvMethod)
    {
        return self::send(self::VALIDATION_WARNING_TEMPLATE, $serviceId, [
            'domain'     => $domain,
            'dcv_method' => strtoupper($dcvMethod),
        ]);
    }
}
