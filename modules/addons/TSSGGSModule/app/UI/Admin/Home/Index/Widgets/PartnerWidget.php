<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Partner growth manager(s) of the configured vendors (cached in the module's request table)
 */
class PartnerWidget extends Card
{
    public function loadHtml(): void
    {
        $this->start('account-outline', $this->translate('title'));

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
            $html  .= $this->managerHtml((string)($manager['name'] ?? ''), $email, (string)($manager['phone'] ?? ''), (string)($manager['photo'] ?? ''));
        }

        $this->body($html !== '' ? $html : '<p class="tssggs-muted">' . Card::e($this->translate('none')) . '</p>');
    }

    /** Initials avatar; the photo covers it when it loads and removes itself when it does not */
    protected function managerHtml(string $name, string $email, string $phone, string $photo): string
    {
        $initials = '';
        foreach(array_slice(preg_split('/\s+/', trim($name)) ?: [], 0, 2) as $part)
        {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        $img = preg_match('#^https?://#i', $photo) ? '<img src="' . Card::e($photo) . '" alt="" onerror="this.remove()">' : '';

        return '<div class="tssggs-person"><span class="tssggs-person__avatar">' . Card::e($initials) . $img . '</span><div>'
            . '<p class="tssggs-person__name">' . Card::e($name) . '</p>'
            . '<a href="mailto:' . Card::e($email) . '">' . Card::e($email) . '</a>'
            . ($phone !== '' ? '<p class="tssggs-muted">' . Card::e($phone) . '</p>' : '')
            . '</div></div>';
    }
}
