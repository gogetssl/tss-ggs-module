<?php

namespace ModulesGarden\TSSGGSModule\App\Helpers;

use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\EmailTemplate;
use WHMCS\Database\Capsule;

/**
 * Client notifications of the SSL AutoInstall (cPanel) feature.
 * Templates are created on module activation and, if missing, right before sending.
 */
class AutoInstallEmailTemplates
{
    const CERT_INSTALLED_TEMPLATE     = 'The SSL Store & GoGetSSL Module - Certificate Installed';
    const RENEWAL_COMPLETE_TEMPLATE   = 'The SSL Store & GoGetSSL Module - Renewal Complete';
    const DNS_TO_EMAIL_TEMPLATE       = 'The SSL Store & GoGetSSL Module - DNS Validation Switched to Email';
    const AUTO_REISSUE_FAILED_TEMPLATE = 'The SSL Store & GoGetSSL Module - Auto Reissue Failed';
    const VALIDATION_WARNING_TEMPLATE = 'The SSL Store & GoGetSSL Module - Validation Warning';
    const RENEWAL_STARTED_TEMPLATE    = 'The SSL Store & GoGetSSL Module - Renewal Started';
    const RENEWAL_AUTORENEWED_TEMPLATE = 'The SSL Store & GoGetSSL Module - Certificate Autorenewed';

    protected static function getDefinitions()
    {
        return [
            self::CERT_INSTALLED_TEMPLATE => [
                'SSL Certificate Installed for {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>Your SSL certificate for <strong>{$domain}</strong> has been successfully issued and installed on your server. No further action is required.</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#27ae60;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
                . '<p>{$signature}</p>'
            ],
            self::RENEWAL_COMPLETE_TEMPLATE => [
                'SSL Certificate Renewed Successfully - {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>Your SSL certificate for <strong>{$domain}</strong> has been renewed and installed successfully. No further action is required.</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#27ae60;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
                . '<p>{$signature}</p>'
            ],
            self::DNS_TO_EMAIL_TEMPLATE => [
                'Action Required: Email Validation Needed for {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>We attempted DNS-based domain validation for <strong>{$domain}</strong> but could not complete it automatically.</p>'
                . '<p>Email validation has been initiated instead. An approval email has been sent to:</p>'
                . '<p><strong>{$dcv_email}</strong></p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Manage SSL Order</a></p>'
                . '<p>{$signature}</p>'
            ],
            self::AUTO_REISSUE_FAILED_TEMPLATE => [
                'Action Required: SSL Certificate Reissue Failed - {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>We were unable to automatically reissue your SSL certificate for <strong>{$domain}</strong>.</p>'
                . '<table style="border-collapse:collapse;margin:16px 0;">'
                . '<tr><td style="padding:6px 14px 6px 0;color:#666;font-weight:600;white-space:nowrap;">Certificate expiry:</td>'
                . '<td style="padding:6px 0;">{$cert_expiry} ({$days_left} days remaining)</td></tr>'
                . '<tr><td style="padding:6px 14px 6px 0;color:#666;font-weight:600;white-space:nowrap;">Error:</td>'
                . '<td style="padding:6px 0;color:#c0392b;">{$error_message}</td></tr>'
                . '</table>'
                . '<p>Please contact support so we can resolve this promptly.</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
                . '<p>{$signature}</p>'
            ],
            self::VALIDATION_WARNING_TEMPLATE => [
                'Action Required: SSL Certificate Validation Issue for {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>We are having trouble automatically validating your SSL certificate for the domain <strong>{$domain}</strong>.</p>'
                . '<p>The validation file was uploaded to your server using the <strong>{$dcv_method} file method</strong>, '
                . 'but the Certificate Authority has not been able to verify it after several attempts.</p>'
                . '<p>This can happen when:</p>'
                . '<ul>'
                . '<li>The file is blocked by a redirect or <code>.htaccess</code> rule (e.g. forced HTTPS or www redirect)</li>'
                . '<li>A CDN or proxy (e.g. Cloudflare) is caching or blocking the path</li>'
                . '<li>The web server is not configured to serve files without extensions</li>'
                . '</ul>'
                . '<p><strong>What to do:</strong> Please log in to your account and switch the validation method to <strong>Email Approval</strong>. '
                . 'An approval email will be sent to an address at your domain (e.g. admin@{$domain}), '
                . 'and clicking the link in that email will complete validation immediately.</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Manage SSL Certificate</a></p>'
                . '<p>If you need help, please contact our support team.</p>'
                . '<p>{$signature}</p>'
            ],
            self::RENEWAL_STARTED_TEMPLATE => [
                'SSL Certificate Renewal Started - {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>Renewal has been initiated for your SSL certificate on <strong>{$domain}</strong>.</p>'
                . '<p>Current expiry: <strong>{$cert_expiry}</strong> ({$days_left} days remaining).</p>'
                . '<p>You will receive a confirmation email once the renewed certificate has been installed.</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">View SSL Order</a></p>'
                . '<p>{$signature}</p>'
            ],
            self::RENEWAL_AUTORENEWED_TEMPLATE => [
                'SSL Certificate Autorenewed for {$domain}',
                '<p>Dear {$client_name},</p>'
                . '<p>Your SSL certificate order <strong>{$order_number}</strong> for <strong>{$common_name}</strong> has been autorenewed and a new order has been placed with the Certificate Authority.</p>'
                . '<p>If you need to make updates to the order or download the certificate, visit the order page:</p>'
                . '<p><a href="{$order_url}" style="display:inline-block;padding:10px 22px;background:#2980b9;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">{$order_url}</a></p>'
                . '<p>{$signature}</p>'
            ],
        ];
    }

    public static function createTemplates()
    {
        foreach(self::getDefinitions() as $name => $definition)
        {
            self::createTemplate($name, $definition[0], $definition[1]);
        }
    }

    protected static function createTemplate($name, $subject, $message)
    {
        if(EmailTemplate::where('name', $name)->first())
        {
            return;
        }

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

    protected static function send($template, $serviceId, array $vars)
    {
        $definitions = self::getDefinitions();
        if(isset($definitions[$template]))
        {
            self::createTemplate($template, $definitions[$template][0], $definitions[$template][1]);
        }

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
