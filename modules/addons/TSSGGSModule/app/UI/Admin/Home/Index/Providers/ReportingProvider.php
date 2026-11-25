<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Forms\DownloadLogForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\LogProvider;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;


class ReportingProvider extends CrudProvider
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

    public function read()
    {
        $this->data = $this->formData;
        global $CONFIG;

        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        $this->data['sendTo']        = self::supportAddress();
        $this->data['whmcsUrl']      = $CONFIG['SystemURL'];
        $this->data['moduleVersion'] = Config::get('configuration.version');
        $this->data['tssPartnerId']  = trim((string)($moduleConfiguration['credentials']['tss']['PartnerCode'] ?? ''));
        $this->data['ggsPartnerId']  = trim((string)($moduleConfiguration['credentials']['ggs']['PartnerCode'] ?? ''));
        $this->data['phpVersion']    = phpversion();
        $this->data['fromDate']      = date('Y-m-d', strtotime("-7 days"));
        $this->data['toDate']        = date('Y-m-d');
        $this->data['cronStatus']    = LogProvider::cronStatus();
    }

    public function create()
    {

    }

    public function send()
    {
        global $CONFIG;

        $data  = $this->formData->toArray();
        $admin = self::currentAdmin();
        // General Settings > Email Address is the sender of WHMCS mail (SystemEmailsFromEmail is an older key without a field)
        $from  = trim((string)\WHMCS\Config\Setting::getValue('Email')) ?: trim((string)\WHMCS\Config\Setting::getValue('SystemEmailsFromEmail'));
        if($from === '')
        {
            throw new \Exception('Set the e-mail address in WHMCS (Configuration > System Settings > General Settings > Email Address) to send the report.');
        }

        // the read-only fields are not posted: the report always carries the values of this moment
        $data['whmcsUrl']      = (string)$CONFIG['SystemURL'];
        $data['moduleVersion'] = (string)Config::get('configuration.version');
        $data['phpVersion']    = phpversion();
        $data['cronStatus']    = LogProvider::cronStatus();
        $data['issueDetails']  = html_entity_decode((string)($data['issueDetails'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // the layout of the activity log report of the earlier TheSSLStore WHMCS plugin, which support knows
        $partnerCode = trim(implode(', ', array_filter([(string)($data['ggsPartnerId'] ?? ''), (string)($data['tssPartnerId'] ?? '')])));
        $date        = function($value) { $time = strtotime((string)$value); return $time ? date('m/d/Y', $time) : (string)$value; };
        $rows        = [
            'WHMCS URL'                  => $data['whmcsUrl'],
            'Partner ID'                 => $partnerCode,
            'Production Mode(api_mode)'  => self::apiMode(),
            'Issue Date'                 => $date($data['fromDate'] ?? '') . ' - ' . $date($data['toDate'] ?? ''),
            'Issue Detail'               => $data['issueDetails'],
            'Module Version'             => Edition::name() . ' ' . $data['moduleVersion'],
            'PHP Version'                => $data['phpVersion'],
            'WHMCS Version'              => (string)($CONFIG['Version'] ?? ''),
            'Cron Status'                => $data['cronStatus'],
            'Reported By'                => trim($admin['name'] . ($admin['email'] !== '' ? ' (' . $admin['email'] . ')' : '')),
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
        $message->setFromName(trim(($admin['name'] ?: (string)$CONFIG['CompanyName']) . ($partnerCode !== '' ? ' (' . $partnerCode . ')' : '')));
        $message->addStringAttachment('activity_log.txt', (new LogProvider())->getLog($data));

        \WHMCS\Module\Mail::factory()->send($message);

        // the dialog closes once the report is sent
        return (new Response())
            ->setSuccess($this->translate('reportSent', ['address' => self::supportAddress()]))
            ->setActions([new \ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose(new \ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal())]);
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
            $key           = strtolower($vendor);
            $modes[$key]   = $key === 'tss' && ($credentials['tss']['OperationMode'] ?? '') === 'sandbox' ? 'SANDBOX' : 'LIVE';
        }

        return count(array_unique($modes)) === 1 ? reset($modes) : implode(', ', array_map(function($k, $v) { return strtoupper($k) . ' ' . $v; }, array_keys($modes), $modes));
    }

    /** the changed module files, one per line, or "No Files Modified..." */
    protected static function modifiedFiles(): string
    {
        $compare = \ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers::compareChecksumData();
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
            ? \WHMCS\Database\Capsule::table('tbladmins')->where('id', (int)$_SESSION['adminid'])->first(['firstname', 'lastname', 'email'])
            : null;

        return [
            'name'  => $admin ? trim($admin->firstname . ' ' . $admin->lastname) : '',
            'email' => $admin && filter_var($admin->email, FILTER_VALIDATE_EMAIL) ? (string)$admin->email : '',
        ];
    }

    public function download()
    {
        $data               = $this->formData->toArray();
        $data['cronStatus'] = LogProvider::cronStatus();

        return (new Response())
            ->setSuccess($this->translate('logDownloadedSuccessfully'))
            ->setActions([
                             new DownloadFileFromForm(new DownloadLogForm(), $data),
                         ]);
    }
}
