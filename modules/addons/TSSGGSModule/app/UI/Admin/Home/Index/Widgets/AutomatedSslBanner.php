<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Container\Container;

/**
 * Automated SSL promotion (design: automated-ssl-banner.html). Shown on the dashboard instead of the certificate
 * pipeline until a CaaS and an AIS product are enabled (see AutomatedSsl::enabled()).
 * Styles in resources/assets/css/theme.css, scoped to .ssl-promo.
 */
class AutomatedSslBanner extends Container
{
    const MESSAGES = 4;

    public function loadHtml(): void
    {
        $this->setCss('tssggs-promo-wrap');

        $check = '<span class="ssl-promo-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg></span>';
        $msgs  = '';
        for($i = 1; $i <= self::MESSAGES; $i++)
        {
            $msgs .= '<p class="ssl-promo-msg">' . Card::e($this->translate('message_' . $i)) . '</p>';
        }

        $html = '<section class="ssl-promo" aria-label="' . Card::e($this->translate('label')) . '">'
            . '<div class="ssl-promo-halo" aria-hidden="true"></div>'
            . '<div class="ssl-promo-content">'
            . '<div class="ssl-promo-copy">'
            . '<p class="ssl-promo-eyebrow">' . Card::e($this->translate('eyebrow')) . '</p>'
            . '<h3 class="ssl-promo-title">' . Card::e($this->translate('title')) . '</h3>'
            . '<div class="ssl-promo-messages" aria-live="off">' . $msgs . '</div>'
            . '<div class="ssl-promo-chips">'
            . '<span class="ssl-promo-chip">' . $check . Card::e($this->translate('chip_issue')) . '</span>'
            . '<span class="ssl-promo-chip">' . $check . Card::e($this->translate('chip_renew')) . '</span>'
            . '<span class="ssl-promo-chip">' . $check . Card::e($this->translate('chip_install')) . '</span>'
            . '</div></div>'
            . '<div class="ssl-promo-actions">'
            . '<div class="ssl-promo-shield" aria-hidden="true"><span class="ssl-promo-ring"></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-8 9-8 9s-8-4-8-9V5l8-3 8 3z"/><path d="m9 12 2 2 4-4"/></svg></div>'
            . '<div><a class="ssl-promo-cta" href="addonmodules.php?module=TSSGGSModule&amp;mg-page=products">' . Card::e($this->translate('cta'))
            . '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>'
            . '<p class="ssl-promo-note">' . Card::e($this->translate('note')) . '</p></div>'
            . '</div></div></section>';

        $this->addElement(Card::html($html));
    }
}
