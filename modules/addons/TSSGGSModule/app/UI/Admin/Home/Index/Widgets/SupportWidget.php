<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

/**
 * Help: the support contacts and documentation of the edition (TheSSLStore: phone + e-mail, GoGetSSL: e-mail), the
 * partner growth manager (cached in the module's request table) and the activity report for support (opens the
 * report modal). One list of rows; the report row holds a framework button, so it is a component inside the list.
 */
class SupportWidget extends Card
{
    /** edition => contacts (no phone: the row is left out) */
    const CONTACTS = [
        Edition::TSS => ['email' => 'support@thesslstore.com', 'phone' => '727-388-4240', 'docs' => 'https://github.com/thestores/whmcs/'],
        Edition::GGS => ['email' => 'support@gogetssl.com', 'phone' => '', 'docs' => 'https://github.com/gogetssl/whmcs'],
    ];

    public function loadHtml(): void
    {
        $this->start('lifebuoy', $this->translate('title'));

        $contact = self::CONTACTS[Edition::get()];
        $html    = '';
        if($contact['phone'] !== '')
        {
            $html .= Card::row('phone-outline', $this->translate('phone'), $this->translate('phone_detail'), '<b>' . Card::e($contact['phone']) . '</b>');
        }
        $html .= Card::row('email-outline', $this->translate('email'), $contact['email'], Card::button('mailto:' . $contact['email'], $this->translate('write')));
        $html .= Card::row('book-open-page-variant-outline', $this->translate('docs'), $this->translate('docs_detail'), Card::button($contact['docs'], $this->translate('open'), '', true));
        $html .= $this->managers();

        $rows = new Container();
        $rows->setCss('tssggs-dcard__rows');
        $rows->addElement(Card::html($html));

        // activity report: a framework button (it opens a modal), the last row of the same list
        $report = new Button();
        $report->setTitle($this->translate('report_button'));
        $report->onClick(new ModalLoad(new ReportingModal()));

        $row = new Container();
        $row->setCss('tssggs-row tssggs-row--component');
        $row->addElement(Card::html(Card::icon('file-document-outline') . '<span class="tssggs-row__name">' . Card::e($this->translate('report')) . '<small>' . Card::e($this->translate('report_detail')) . '</small></span>'));
        $row->addElement($report);
        $rows->addElement($row);

        $this->addElement($rows);
    }

    /** Partner growth manager(s) of the configured gateways, one row each */
    protected function managers(): string
    {
        $country = (string)\WHMCS\Config\Setting::getValue('DefaultCountry');
        $seen    = [];
        $html    = '';
        foreach(Helpers::getConfiguredApis() as $api)
        {
            try
            {
                $manager = (array)$api->getManager($country);
            }
            catch(\Throwable $e)
            {
                continue;
            }
            $email = (string)($manager['email'] ?? '');
            if($email === '' || in_array($email, $seen, true))
            {
                continue;
            }
            $seen[] = $email;
            $html  .= Card::row('account-tie-outline', (string)($manager['name'] ?? $email), $this->translate('manager'), Card::button('mailto:' . $email, $this->translate('write')));
        }

        return $html;
    }
}
