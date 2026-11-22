<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index;

use ModulesGarden\TSSGGSModule\App\Libs\AutomatedSsl;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\SalesSummary;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\AnnouncementsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\AttentionWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\AutomatedSslBanner;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\GatewayWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\MigrationWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\NextStepsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\OverviewWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\PipelineWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SupportWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\SystemCheckWidget;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

/**
 * Module dashboard: heading, setup strip (first 72 hours), KPI tiles, then rows of a wide and a narrow panel:
 * pipeline + system health, needs attention + gateway, sales and margin + migration, announcements + help.
 * Styles in resources/assets/css/dashboard.css and theme.css, scoped to .tssggs-dash.
 */
class HomeContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setCss('tssggs-dash');

        $this->addElement(Card::html($this->headHtml()));
        $update = $this->updateHtml();
        if($update !== '')
        {
            $this->addElement(Card::html($update));
        }
        // setup guide until all three steps are done
        if(NextStepsWidget::visible())
        {
            $this->addElement(new NextStepsWidget());
        }
        $this->addElement(Card::html($this->kpisHtml()));

        // the Automated SSL banner until a CaaS and an AIS product are enabled, then the certificate pipeline
        $this->addElement($this->row(AutomatedSsl::enabled() ? new PipelineWidget() : new AutomatedSslBanner(), new SystemCheckWidget()));
        $this->addElement($this->row(new AttentionWidget(), new GatewayWidget()));
        $this->addElement($this->row(new OverviewWidget(), MigrationWidget::relevant() ? new MigrationWidget() : null));
        $this->addElement($this->row(new AnnouncementsWidget(), new SupportWidget()));

        $this->addElement(Card::html($this->footHtml()));
    }

    /**
     * "Version X is available" with a link to the update page. The cron looks once a day; without a cron the
     * dashboard looks itself when the last check is older than three days.
     */
    protected function updateHtml(): string
    {
        try
        {
            $state = \ModulesGarden\TSSGGSModule\App\Libs\Update\State::get();
            if(time() - (int)($state['checkedAt'] ?? 0) > 3 * 86400)
            {
                $state = \ModulesGarden\TSSGGSModule\App\Libs\Update\Release::check(true);
            }
            if(!\ModulesGarden\TSSGGSModule\App\Libs\Update\Release::available($state))
            {
                return '';
            }
        }
        catch(\Throwable $e)
        {
            return '';
        }
        $t = function($key, array $vars = []) { return Card::e($this->translate($key, $vars, ['admin.module_update'])); };

        return '<div class="tssggs-upd-banner">' . Card::icon('package-variant-closed')
            . '<span>' . $t('banner', [':version' => (string)$state['version'], ':installed' => \ModulesGarden\TSSGGSModule\App\Libs\Update\State::installedVersion()]) . '</span>'
            . '<a href="addonmodules.php?module=TSSGGSModule&amp;mg-page=moduleUpdate">' . $t('banner_link') . '</a></div>';
    }

    /** A wide panel and, when given, a narrow one beside it */
    protected function row(Container $wide, Container $narrow = null): Container
    {
        $row = new Container();
        $row->setCss('tssggs-dash__row' . ($narrow ? '' : ' is-single'));
        $row->addElement($wide);
        if($narrow)
        {
            $row->addElement($narrow);
        }

        return $row;
    }

    protected function headHtml(): string
    {
        return '<div class="tssggs-dash__head"><div>'
            . '<div class="tssggs-dash__crumb">' . Card::e(Edition::name()) . Card::icon('chevron-right') . Card::e($this->translate('title')) . '</div>'
            . '<h2>' . Card::e($this->translate('title')) . '</h2></div>'
            . '</div>';
    }

    protected function kpisHtml(): string
    {
        $sales    = SalesSummary::get();
        $currency = (string)Helpers::getDefaultCurrencyCode();
        $period   = SalesSummary::periodLabel();
        $tiles    = [
            ['kpi_sales', Helpers::formatDefaultCurrency($sales['paid']), 'currency-usd', 'ok', 'credit-card-outline'],
            ['kpi_profit', Helpers::formatDefaultCurrency($sales['profit']), 'trending-up', 'info', 'chart-line'],
            ['kpi_issued', (string)$sales['issued'], 'certificate-outline', 'ok', 'calendar-month-outline'],
            ['kpi_expiring', (string)$sales['expiring'], 'timer-sand', 'warn', 'check-circle-outline'],
        ];

        $html = '<section class="tssggs-dash__kpis" aria-label="' . Card::e($period) . '">';
        foreach($tiles as [$key, $value, $icon, $tone, $detailIcon])
        {
            $html .= '<article class="tssggs-kpi"><div class="tssggs-kpi__top"><h3>' . Card::e($this->translate($key)) . '</h3>' . Card::tile($icon, $tone) . '</div>'
                . '<p class="tssggs-kpi__value">' . Card::e($value) . '</p>'
                . '<p class="tssggs-kpi__detail">' . Card::icon($detailIcon) . Card::e($this->translate($key . '_detail', ['currency' => $currency, 'period' => $period])) . '</p></article>';
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
