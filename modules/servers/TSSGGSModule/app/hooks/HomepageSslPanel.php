<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * SSL summary panel on the client area home page
 *
 * WHMCS hook ClientAreaHomepagePanels (registered in hooks.php).
 */
class HomepageSslPanel
{
    public static function handle($homePagePanels)
    {
        try
        {
            if(empty($_SESSION['uid']) || !\TSSGGSModule\App\Libs\Support\GeneralSettings::enabled('clientSummary'))
            {
                return;
            }

            $counts = (new \TSSGGSModule\App\Libs\Certificates\ClientSslSummary($_SESSION['uid']))->counts();
            if(!$counts['total'])
            {
                return;
            }

            $t     = \TSSGGSModule\App\ClientArea\SslPages::texts();
            $icons = ['unpaid' => 'fa-credit-card', 'awaiting' => 'fa-edit', 'processing' => 'fa-cogs', 'expires_soon' => 'fa-hourglass-half'];
            $body  = '<div class="tssggs-summary" style="display:flex;flex-wrap:wrap;text-align:center;">';
            foreach($icons as $type => $icon)
            {
                $body .= '<a href="index.php?m=TSSGGSModule&amp;type=' . $type . '" style="flex:1 1 25%;padding:15px 5px;text-decoration:none;">'
                    . '<i class="fas ' . $icon . '" style="font-size:22px;display:block;margin-bottom:6px;"></i>'
                    . '<span style="display:block;">' . $t[$type] . '</span><strong style="font-size:20px;">' . (int)$counts[$type] . '</strong></a>';
            }
            $body .= '</div>';

            $panel = $homePagePanels->addChild('tssggs_ssl_summary', [
                'label'    => $t['summaryTitle'],
                'icon'     => 'fa-lock',
                'order'    => 60,
                'extras'   => [
                    'color'    => 'gold',
                    'btn-link' => 'index.php?m=TSSGGSModule&type=total',
                    'btn-text' => $t['viewAll'],
                    'btn-icon' => 'fas fa-arrow-right',
                ],
                'bodyHtml' => $body,
            ]);
            unifiedSslLabelBlock($panel, $t['summaryTitle'], \TSSGGSModule\App\Libs\Support\Edition::brand(), 'panel');
        }
        catch(\Throwable $e)
        {
            logActivity('TSSGGSModule SSL summary panel: ' . $e->getMessage());
        }
    }
}
