<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
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
        $from  = trim((string)\WHMCS\Config\Setting::getValue('SystemEmailsFromEmail')) ?: trim((string)\WHMCS\Config\Setting::getValue('Email'));
        if($from === '')
        {
            throw new \Exception('Set the system e-mail address in WHMCS (Configuration > System Settings > General Settings > Mail) to send the report.');
        }

        // the read-only fields are not posted: the report always carries the values of this moment
        $data['whmcsUrl']      = (string)$CONFIG['SystemURL'];
        $data['moduleVersion'] = (string)Config::get('configuration.version');
        $data['phpVersion']    = phpversion();
        $data['cronStatus']    = LogProvider::cronStatus();
        $data['issueDetails']  = html_entity_decode((string)($data['issueDetails'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // the issue text is decoded above (WHMCS hands request data HTML-encoded)
        $e    = function($text) { return nl2br(htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8')); };
        $rows = [
            'Company'        => (string)$CONFIG['CompanyName'],
            'WHMCS URL'      => $data['whmcsUrl'],
            'Reported by'    => trim($admin['name'] . ($admin['email'] !== '' ? ' <' . $admin['email'] . '>' : '')),
            'Module'         => Edition::name() . ' ' . $data['moduleVersion'],
            'Partner ID'     => trim(implode(', ', array_filter([(string)($data['ggsPartnerId'] ?? ''), (string)($data['tssPartnerId'] ?? '')]))),
            'PHP'            => $data['phpVersion'],
            'CRON status'    => $data['cronStatus'],
            'Log period'     => ($data['fromDate'] ?? '') . ' - ' . ($data['toDate'] ?? ''),
        ];
        $html  = 'Dear Support,<br><br>A new issue was reported from ' . $e($CONFIG['CompanyName']) . '.<br><br><b>Issue details</b><br>' . $e($data['issueDetails'] ?? '') . '<br><br>';
        foreach($rows as $label => $value)
        {
            if($value === '')
            {
                continue;
            }
            $html  .= '<b>' . $label . ':</b> ' . $e($value) . '<br>';
        }
        $html  .= '<br>The module log of the period is attached (log.txt).';

        $message = new \WHMCS\Mail\Message();
        $message->setSubject('NEW WHMCS ' . Edition::name() . ' issue reported - ' . $CONFIG['CompanyName']);
        $message->setBodyAndPlainText($html);
        $message->clearRecipients('to');
        $message->addRecipient('to', self::supportAddress());
        // mail providers such as Mailgun refuse a message without a sender address
        $message->setFromEmail($from);
        $message->setFromName((string)(\WHMCS\Config\Setting::getValue('SystemEmailsFromName') ?: $CONFIG['CompanyName']));
        // support answers the administrator who sent the report
        if($admin['email'] !== '')
        {
            $message->setReplyTo($admin['email'], $admin['name']);
        }
        $message->addStringAttachment('log.txt', (new LogProvider())->getLog($data));

        \WHMCS\Module\Mail::factory()->send($message);

        return (new Response())->setSuccess($this->translate('reportSent', ['address' => self::supportAddress()]));
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
