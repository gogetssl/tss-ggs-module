<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\GatewayHealth;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Pipeline;

/**
 * Needs your attention: failing or never-run tasks, an unreachable gateway and orders waiting for the client, each
 * with the page that fixes it
 */
class AttentionWidget extends Card
{
    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=';

    public function loadHtml(): void
    {
        $items = $this->items();
        $this->start('alert-outline', $this->translate('title'),
            '<span class="tssggs-dcard__aside">' . Card::e($this->translate(count($items) === 1 ? 'count_one' : 'count', ['n' => count($items)])) . '</span>',
            $items ? 'warn' : 'ok');

        if(!$items)
        {
            $this->body('<div class="tssggs-alerts"><div class="tssggs-alertrow">' . Card::tile('check', 'ok')
                . '<div><h4>' . Card::e($this->translate('none')) . '</h4><p>' . Card::e($this->translate('none_detail')) . '</p></div></div></div>');

            return;
        }

        $html = '';
        foreach($items as [$tone, $icon, $title, $text, $button])
        {
            $html .= '<div class="tssggs-alertrow">' . Card::tile($icon, $tone) . '<div><h4>' . Card::e($title) . '</h4><p>' . Card::e($text) . '</p></div>' . $button . '</div>';
        }
        $this->body('<div class="tssggs-alerts">' . $html . '</div>');
    }

    /** [tone, icon, title, text, button HTML] */
    protected function items(): array
    {
        $items = [];
        foreach(CronStatus::rows() as $task => $row)
        {
            $name = $this->translate('name_' . $task);
            $name = strpos($name, 'name_') !== false ? $task : $name;
            if($row['last'] === null)
            {
                $items[] = ['bad', 'close', $this->translate('never_title', ['task' => $name]), $this->translate('never_text'),
                    Card::button(self::BASE . 'settings&settings-page=cronSettings', $this->translate('cron'), 'cog-outline')];
            }
            elseif($row['error'] !== '')
            {
                $items[] = ['bad', 'close', $this->translate('failing_title', ['task' => $name]), $row['error'],
                    Card::button(self::BASE . 'logs', $this->translate('log'), 'format-list-checks')];
            }
        }

        // the gateway of this edition: unreachable, or its credentials are not entered
        $vendor  = GatewayWidget::vendor();
        $gateway = GatewayHealth::check()[$vendor] ?? null;
        if($gateway === null)
        {
            $items[] = ['bad', 'power-plug-off-outline', $this->translate('gateway_missing_title', ['gateway' => Helpers::vendorToDisplay($vendor)]), $this->translate('gateway_missing_text'),
                Card::button(self::BASE . 'settings&settings-page=apiSettings', $this->translate('api'), 'key-outline')];
        }
        elseif(!$gateway['ok'])
        {
            $items[] = ['bad', 'power-plug-off-outline', $this->translate('gateway_title', ['gateway' => Helpers::vendorToDisplay($vendor)]), $gateway['error'],
                Card::button(self::BASE . 'settings&settings-page=apiSettings', $this->translate('api'), 'key-outline')];
        }

        $pipeline = Pipeline::get();
        if($pipeline['configure'] > 0)
        {
            $items[] = ['warn', 'file-document-outline', $this->translate($pipeline['configure'] === 1 ? 'configure_title_one' : 'configure_title', ['n' => $pipeline['configure']]),
                $this->translate('configure_text'), Card::button(self::BASE . 'reports', $this->translate('orders'), 'arrow-right')];
        }

        return $items;
    }
}
