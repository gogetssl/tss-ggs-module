<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\GatewayHealth;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * API Gateway connection: reachability and response time of the gateway of this edition (GoGetSSL or TheSSLStore),
 * link to the API credentials
 */
class GatewayWidget extends Card
{
    /** Gateway key of the edition: GGS | TSS */
    public static function vendor(): string
    {
        return Edition::get() === Edition::TSS ? 'TSS' : 'GGS';
    }

    public function loadHtml(): void
    {
        // the edition's own vendor, and the connected one (Settings > API, "Connect ... products")
        $results = GatewayHealth::check(isset($_GET['recheck']));
        $api     = 'addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=apiSettings';
        $allOk   = true;
        $html    = '';
        foreach(Edition::vendors() as $key)
        {
            $vendor = strtoupper($key);
            $row    = $results[$vendor] ?? null;
            $allOk  = $allOk && $row && $row['ok'];
            if($row === null)
            {
                // the credentials of this vendor are not entered
                $html .= Card::row('web', Helpers::vendorToDisplay($vendor), $this->translate('not_configured_detail'), Card::status('bad', $this->translate('not_configured'), 'alert-outline'));
                continue;
            }
            $state = $row['ok']
                ? Card::status('ok', $this->translate('connected'), 'check')
                : Card::status('bad', $this->translate('unreachable'), 'close', $row['error']);
            $html .= Card::row('web', Helpers::vendorToDisplay($vendor), $row['ok'] ? '' : $row['error'], $state);
            $html .= Card::row('clock-outline', $this->translate('response'), '', '<b>' . Card::e(number_format(((int)$row['ms']) / 1000, 1) . ' s') . '</b>');
        }
        $this->start('power-plug-outline', $this->translate('title'), '', $allOk ? 'ok' : 'bad');
        $html .= Card::row('lock-outline', $this->translate('credentials'), $this->translate('credentials_detail'), Card::button($api, $this->translate('manage')));
        $this->addElement(Card::html('<div class="tssggs-dcard__rows">' . $html . '</div>'));

        $this->foot('addonmodules.php?module=TSSGGSModule&recheck=1', 'refresh', $this->translate('recheck'), false);
    }
}
