<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Dashboard;

use ModulesGarden\TSSGGSModule\App\Kernel\Layout;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use WHMCS\Database\Capsule;

/**
 * The activity report for support (dashboard, "Send report"): what the dialog shows about this install, the e-mail
 * to the edition's support mailbox with the module log attached, and the log as a file.
 */
class SupportReport
{
    /** edition => the support mailbox the activity report is sent to */
    const SUPPORT = [
        Edition::GGS => 'support@gogetssl.com',
        Edition::TSS => 'apisupport@thesslstore.com',
    ];

    public static function supportAddress(): string
    {
        return self::SUPPORT[Edition::get()] ?? self::SUPPORT[Edition::TSS];
    }

    /** @return array what the dialog shows: the facts of this install and the default dates of the report */
    public static function facts(): array
    {
        $credentials = (array)((new AddonModuleRepository())->getModuleConfiguration()['credentials'] ?? []);

        return [
            'sendTo'        => self::supportAddress(),
            'whmcsUrl'      => (string)($GLOBALS['CONFIG']['SystemURL'] ?? ''),
            'moduleVersion' => Layout::version(),
            'tssPartnerId'  => trim((string)($credentials['tss']['PartnerCode'] ?? '')),
            'ggsPartnerId'  => trim((string)($credentials['ggs']['PartnerCode'] ?? '')),
            'phpVersion'    => phpversion(),
            'cronStatus'    => LogProvider::cronStatus(),
            'fromDate'      => date('Y-m-d', strtotime('-7 days')),
            'toDate'        => date('Y-m-d'),
            'modifiedCount' => self::modifiedCount(),
        ];
    }

    /** @return int module files that were added, changed or removed by hand */
    public static function modifiedCount(): int
    {
        $compare = Helpers::compareChecksumData();

        return count((array)($compare['new'] ?? [])) + count((array)($compare['modified'] ?? [])) + count((array)($compare['deleted'] ?? []));
    }

    /**
     * Sends the report: $form holds fromDate, toDate and issueDetails as typed; everything else is read now.
     *
     * @throws \Exception when WHMCS has no sender address, or the mail could not be sent
     */
    public static function send(array $form): void
    {
        $config = (array)($GLOBALS['CONFIG'] ?? []);
        $data   = self::reportData($form);
        $admin  = self::currentAdmin();
        // General Settings > Email Address is the sender of WHMCS mail (SystemEmailsFromEmail is an older key without a field)
        $from   = trim((string)\WHMCS\Config\Setting::getValue('Email')) ?: trim((string)\WHMCS\Config\Setting::getValue('SystemEmailsFromEmail'));
        if($from === '')
        {
            throw new \Exception('Set the e-mail address in WHMCS (Configuration > System Settings > General Settings > Email Address) to send the report.');
        }

        // the layout of the activity log report of the earlier TheSSLStore WHMCS plugin, which support knows
        $partnerCode = trim(implode(', ', array_filter([$data['ggsPartnerId'], $data['tssPartnerId']])));
        $date        = function($value) {
            $time = strtotime((string)$value);

            return $time ? date('m/d/Y', $time) : (string)$value;
        };
        $rows = [
            'WHMCS URL'                 => $data['whmcsUrl'],
            'Partner ID'                => $partnerCode,
            'Production Mode(api_mode)' => self::apiMode(),
            'Issue Date'                => $date($data['fromDate']) . ' - ' . $date($data['toDate']),
            'Issue Detail'              => $data['issueDetails'],
            'Module Version'            => Edition::name() . ' ' . $data['moduleVersion'],
            'PHP Version'               => $data['phpVersion'],
            'WHMCS Version'             => (string)($config['Version'] ?? ''),
            'Cron Status'               => $data['cronStatus'],
            'Reported By'               => trim($admin['name'] . ($admin['email'] !== '' ? ' (' . $admin['email'] . ')' : '')),
        ];
        $html = '<p>Hello Team,</p><p>We have a WHMCS partner needing assistance with their module.</p><ul>';
        foreach($rows as $label => $value)
        {
            $html .= '<li><strong>' . $label . ': </strong>' . self::text($value) . '</li>';
        }
        $html .= '</ul><p><strong>Modified Files List</strong><br>' . self::text(self::modifiedFiles()) . '</p>';

        $message = new \WHMCS\Mail\Message();
        $message->setSubject('WHMCS Module Activity Log Reporting');
        // the Smarty body: WHMCS renders its e-mail header and footer (plain setBody leaves their Smarty tags as text)
        $message->setBodyFromSmarty($html);
        $message->clearRecipients('to');
        $message->addRecipient('to', self::supportAddress());
        // the administrator gets a copy, as with the earlier plugin
        if($admin['email'] !== '')
        {
            $message->addRecipient('cc', $admin['email'], $admin['name']);
            $message->setReplyTo($admin['email'], $admin['name']);
        }
        // mail providers such as Mailgun refuse a message without a sender address of their domain
        $message->setFromEmail($from);
        $message->setFromName(trim(($admin['name'] ?: (string)($config['CompanyName'] ?? '')) . ($partnerCode !== '' ? ' (' . $partnerCode . ')' : '')));
        $message->addStringAttachment('activity_log.txt', (new LogProvider())->getLog($data));
        \WHMCS\Module\Mail::factory()->send($message);
    }

    /** @return string the module log of the report's dates, as the text file support gets */
    public static function log(array $form): string
    {
        return (string)(new LogProvider())->getLog(self::reportData($form));
    }

    /** The facts of this moment plus what the admin typed (dates, issue details) */
    protected static function reportData(array $form): array
    {
        $day = function($value, $default) {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) ? (string)$value : $default;
        };
        $facts = self::facts();

        return [
            'fromDate'     => $day($form['fromDate'] ?? '', $facts['fromDate']),
            'toDate'       => $day($form['toDate'] ?? '', $facts['toDate']),
            // WHMCS hands request data HTML-encoded
            'issueDetails' => html_entity_decode((string)($form['issueDetails'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ] + $facts;
    }

    /** a value for the Smarty body: HTML escaped, line breaks kept, braces as entities so Smarty leaves them alone */
    protected static function text($value): string
    {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], nl2br(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8')));
    }

    /** LIVE or SANDBOX per vendor of this edition (GoGetSSL has no sandbox) */
    protected static function apiMode(): string
    {
        $credentials = (array)((new AddonModuleRepository())->getModuleConfiguration()['credentials'] ?? []);
        $modes       = [];
        foreach(Edition::vendors() as $vendor)
        {
            $key         = strtolower($vendor);
            $modes[$key] = $key === 'tss' && ($credentials['tss']['OperationMode'] ?? '') === 'sandbox' ? 'SANDBOX' : 'LIVE';
        }

        return count(array_unique($modes)) === 1 ? reset($modes) : implode(', ', array_map(function($k, $v) {
            return strtoupper($k) . ' ' . $v;
        }, array_keys($modes), $modes));
    }

    /** the changed module files, one per line, or "No Files Modified..." */
    protected static function modifiedFiles(): string
    {
        $compare = Helpers::compareChecksumData();
        $lines   = [];
        foreach(['new', 'modified', 'deleted'] as $kind)
        {
            foreach((array)($compare[$kind] ?? []) as $file)
            {
                $lines[] = ucfirst($kind) . ': ' . $file;
            }
        }

        return $lines ? implode("\n", $lines) : 'No Files Modified...';
    }

    /** ['name', 'email'] of the logged-in administrator */
    protected static function currentAdmin(): array
    {
        $admin = !empty($_SESSION['adminid'])
            ? Capsule::table('tbladmins')->where('id', (int)$_SESSION['adminid'])->first(['firstname', 'lastname', 'email'])
            : null;

        return [
            'name'  => $admin ? trim($admin->firstname . ' ' . $admin->lastname) : '',
            'email' => $admin && filter_var($admin->email, FILTER_VALIDATE_EMAIL) ? (string)$admin->email : '',
        ];
    }
}
