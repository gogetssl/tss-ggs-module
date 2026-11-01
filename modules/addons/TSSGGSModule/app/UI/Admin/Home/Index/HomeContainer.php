<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\SalesSummary;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\AnnouncementsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\DocumentationWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\MigrationWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\NextStepsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\OverviewWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\PartnerWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\ReportingWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SupportWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SystemCheckWidget;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

/**
 * Module dashboard (design: github.com/gogetssl/ssl-plugin-refresh, dashboard route; the module menu stays the
 * framework's own). Heading, cron attention banner, KPI tiles, two card columns, footer.
 * Styles in resources/assets/css/dashboard.css, scoped to .tssggs-dash.
 */
class HomeContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setCss('tssggs-dash');

        $this->addElement(Card::html($this->headHtml()));
        if(!CronStatus::ok())
        {
            $this->addElement(Card::html($this->attentionHtml()));
        }
        // guide for the first 72 hours
        if(NextStepsWidget::visible())
        {
            $this->addElement(new NextStepsWidget());
        }
        $this->addElement(Card::html($this->kpisHtml()));

        $main = new Container();
        $main->setCss('tssggs-dash__col');
        $main->addElement(new OverviewWidget())
             ->addElement(new AnnouncementsWidget())
             ->addElement(new SupportWidget());

        $side = new Container();
        $side->setCss('tssggs-dash__col');
        $side->addElement(new SystemCheckWidget());
        if(MigrationWidget::relevant())
        {
            $side->addElement(new MigrationWidget());
        }
        $side->addElement(new PartnerWidget())
             ->addElement(new ReportingWidget())
             ->addElement(new DocumentationWidget());

        $grid = new Container();
        $grid->setCss('tssggs-dash__grid');
        $grid->addElement($main)->addElement($side);
        $this->addElement($grid);

        $this->addElement(Card::html($this->footHtml()));
    }

    protected function headHtml(): string
    {
        return '<div class="tssggs-dash__head"><div>'
            . '<div class="tssggs-dash__crumb">' . Card::e(Edition::name()) . Card::icon('chevron-right') . Card::e($this->translate('title')) . '</div>'
            . '<h2>' . Card::e($this->translate('title')) . '</h2></div>'
            . '<span class="tssggs-dash__period">' . Card::icon('calendar-month-outline') . Card::e($this->translate('period')) . '</span>'
            . '</div>';
    }

    protected function attentionHtml(): string
    {
        return '<div class="tssggs-dash__alert" role="alert">' . Card::icon('alert-outline')
            . '<div><strong>' . Card::e($this->translate('attention_title')) . '</strong>'
            . '<p>' . Card::e($this->translate('attention_body')) . '</p></div>'
            . Card::link('#tssggs-health', $this->translate('attention_button'), 'is-danger')
            . '</div>';
    }

    protected function kpisHtml(): string
    {
        $sales    = SalesSummary::get();
        $currency = (string)Helpers::getDefaultCurrencyCode();
        $tiles    = [
            ['kpi_sales', Helpers::formatDefaultCurrency($sales['paid']), 'credit-card-outline'],
            ['kpi_profit', Helpers::formatDefaultCurrency($sales['profit']), 'chart-bar'],
            ['kpi_issued', (string)$sales['issued'], 'shield-check-outline'],
            ['kpi_expiring', (string)$sales['expiring'], 'clock-outline'],
        ];

        $html = '<section class="tssggs-dash__kpis" aria-label="' . Card::e($this->translate('period')) . '">';
        foreach($tiles as [$key, $value, $icon])
        {
            $html .= '<article class="tssggs-kpi"><div class="tssggs-kpi__top"><h3>' . Card::e($this->translate($key)) . '</h3>' . Card::icon($icon) . '</div>'
                . '<p class="tssggs-kpi__value">' . Card::e($value) . '</p>'
                . '<p class="tssggs-kpi__detail">' . Card::e($this->translate($key . '_detail', ['currency' => $currency])) . '</p></article>';
        }

        return $html . '</section>';
    }

    protected function footHtml(): string
    {
        $version = (string)Config::get('configuration.version', '');

        return '<div class="tssggs-dash__foot"><span>' . Card::icon('shield-check-outline') . Card::e(Edition::name()) . '</span>'
            . ($version !== '' ? '<span>' . Card::e($this->translate('version', ['version' => $version])) . '</span>' : '')
            . '</div>';
    }
}
