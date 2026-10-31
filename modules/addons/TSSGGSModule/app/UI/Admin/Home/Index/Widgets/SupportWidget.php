<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Support contacts of the configured vendors
 */
class SupportWidget extends Card
{
    const CONTACTS = [
        'TSS' => ['email' => 'support@thesslstore.com', 'phone' => '727-388-4240'],
        'GGS' => ['email' => 'support@gogetssl.com', 'phone' => ''],
    ];

    public function loadHtml(): void
    {
        $this->setCss('tssggs-dcard tssggs-support');

        $vendors = array_values(array_intersect(array_keys(self::CONTACTS), array_keys(Helpers::getConfiguredApis())));
        $phones  = [];
        $buttons = '';
        foreach($vendors as $vendor)
        {
            $contact = self::CONTACTS[$vendor];
            if($contact['phone'] !== '')
            {
                $phones[] = $this->translate('phone', ['phone' => $contact['phone']]);
            }
            $label    = count($vendors) > 1 ? $this->translate('contact_vendor', ['vendor' => Helpers::vendorToDisplay($vendor)]) : $this->translate('contact');
            $buttons .= '<a class="tssggs-dbtn" href="mailto:' . Card::e($contact['email']) . '">' . Card::icon('email-outline') . Card::e($label) . '</a>';
        }

        $this->addElement(Card::html('<div class="tssggs-support__row"><span class="tssggs-support__icon">' . Card::icon('headset') . '</span>'
            . '<div class="tssggs-support__text"><h3>' . Card::e($this->translate('title')) . '</h3>'
            . ($phones ? '<p>' . Card::e(implode(' · ', $phones)) . '</p>' : '') . '</div>'
            . '<div class="tssggs-support__actions">' . $buttons . '</div></div>'));
    }
}
