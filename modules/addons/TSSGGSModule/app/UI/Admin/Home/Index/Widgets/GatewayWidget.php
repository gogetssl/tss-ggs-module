<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\GatewayHealth;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Gateway connection: reachability and response time of each configured gateway, link to the API credentials
 */
class GatewayWidget extends Card
{
    public function loadHtml(): void
    {
        $results = GatewayHealth::check(isset($_GET['recheck']));
        $ok      = !in_array(false, array_column($results, 'ok'), true);
        $this->start('power-plug-outline', $this->translate('title'), '', $ok ? 'ok' : 'bad');

        $html = '';
        foreach($results as $vendor => $row)
        {
            $state = $row['ok']
                ? Card::status('ok', $this->translate('connected'), 'check')
                : Card::status('bad', $this->translate('unreachable'), 'close', $row['error']);
            $html .= Card::row('web', Helpers::vendorToDisplay($vendor), $row['ok'] ? '' : $row['error'], $state);
            $html .= Card::row('clock-outline', $this->translate('response'), '', '<b>' . Card::e(number_format(((int)$row['ms']) / 1000, 1) . ' s') . '</b>');
        }
        if($html === '')
        {
            $html = Card::row('web', $this->translate('none'), '', '');
        }
        $html .= Card::row('lock-outline', $this->translate('credentials'), $this->translate('credentials_detail'),
            Card::button('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=apiSettings', $this->translate('manage')));
        $this->addElement(Card::html('<div class="tssggs-dcard__rows">' . $html . '</div>'));

        $this->foot('addonmodules.php?module=TSSGGSModule&recheck=1', 'refresh', $this->translate('recheck'), false);
    }
}
