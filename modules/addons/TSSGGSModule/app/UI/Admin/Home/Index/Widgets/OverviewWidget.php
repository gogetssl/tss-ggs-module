<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\SalesSummary;

/**
 * Sales overview: last month's paid / unpaid / overdue amounts
 */
class OverviewWidget extends Card
{
    public function loadHtml(): void
    {
        $this->start('chart-bar', $this->translate('title'),
            Card::link('addonmodules.php?module=TSSGGSModule&mg-page=statistics', $this->translate('statistics'), 'is-ghost'));

        $sales = SalesSummary::get();
        $html  = '<div class="tssggs-split">';
        foreach(['paid' => 'is-ok', 'unpaid' => '', 'overdue' => 'is-bad'] as $key => $tone)
        {
            $html .= '<div><p class="tssggs-label">' . Card::e($this->translate($key)) . '</p>'
                . '<p class="tssggs-amount ' . $tone . '">' . Card::e(Helpers::formatDefaultCurrency($sales[$key])) . '</p></div>';
        }
        $html .= '</div>';

        $this->addElement(Card::html($html . '<div class="tssggs-dcard__note">' . Card::icon('clock-outline') . Card::e($this->translate('period')) . '</div>'));
    }
}
