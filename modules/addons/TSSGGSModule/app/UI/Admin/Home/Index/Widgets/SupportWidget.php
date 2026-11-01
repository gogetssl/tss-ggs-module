<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;

/**
 * Help: support line and e-mail of the configured vendors, the documentation, the partner growth manager (cached in
 * the module's request table) and the activity report for support (opens the report modal)
 */
class SupportWidget extends Card
{
    const CONTACTS = [
        'TSS' => ['email' => 'support@thesslstore.com', 'phone' => '727-388-4240', 'docs' => 'https://github.com/thestores/whmcs/'],
        'GGS' => ['email' => 'support@gogetssl.com', 'phone' => '', 'docs' => 'https://github.com/gogetssl/whmcs'],
    ];

    public function loadHtml(): void
    {
        $this->start('lifebuoy', $this->translate('title'));

        $vendors = array_values(array_intersect(array_keys(self::CONTACTS), array_keys(Helpers::getConfiguredApis())));
        $many    = count($vendors) > 1;
        $html    = '';
        foreach($vendors as $vendor)
        {
            $contact = self::CONTACTS[$vendor];
            $who     = $many ? Helpers::vendorToDisplay($vendor) : '';
            if($contact['phone'] !== '')
            {
                $html .= Card::row('phone-outline', $this->translate('phone'), $who, '<b>' . Card::e($contact['phone']) . '</b>');
            }
            $html .= Card::row('email-outline', $this->translate('email'), $who !== '' ? $who : $contact['email'],
                Card::button('mailto:' . $contact['email'], $this->translate('write')));
            $html .= Card::row('book-open-page-variant-outline', $this->translate('docs'), $many ? $who : $this->translate('docs_detail'),
                Card::button($contact['docs'], $this->translate('open'), '', true));
        }
        $html .= $this->managers();

        $this->addElement(Card::html('<div class="tssggs-dcard__rows">' . $html . '</div>'));

        // activity report: a framework button (it opens a modal), in a row of its own
        $report = new Button();
        $report->setTitle($this->translate('report_button'));
        $report->onClick(new ModalLoad(new ReportingModal()));

        $row = new Container();
        $row->setCss('tssggs-row tssggs-row--component');
        $row->addElement(Card::html(Card::icon('file-document-outline') . '<span class="tssggs-row__name">' . Card::e($this->translate('report')) . '<small>' . Card::e($this->translate('report_detail')) . '</small></span>'));
        $row->addElement($report);
        $this->addElement($row);
    }

    /** Partner growth manager(s) of the configured vendors, one row each */
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
