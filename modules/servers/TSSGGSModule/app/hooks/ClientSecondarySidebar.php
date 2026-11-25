<?php

namespace TSSGGSModule\Server\app\hooks;


/**
 * SSL summary in the secondary sidebar (on all pages, or on the pages listed in the settings)
 *
 * WHMCS hook ClientAreaSecondarySidebar (registered in hooks.php).
 */
class ClientSecondarySidebar
{
    public static function handle($secondarySidebar)
    {
        try
        {
            if(empty($_SESSION['uid']) || !\TSSGGSModule\App\Libs\Support\GeneralSettings::enabled('clientSummary'))
            {
                return;
            }

            global $smarty;
            $template = is_object($smarty) && isset($smarty->tpl_vars['templatefile']) ? (string)$smarty->tpl_vars['templatefile']->value : '';
            $allowed  = array_filter(array_map('trim', explode(',', (string)\TSSGGSModule\App\Libs\Support\GeneralSettings::get('sidebarTemplates'))));
            if($allowed ? !in_array($template, $allowed, true) : $template === 'clientareahome')
            {
                return;
            }

            $counts = (new \TSSGGSModule\App\Libs\Certificates\ClientSslSummary($_SESSION['uid']))->counts();
            if(!$counts['total'])
            {
                return;
            }

            $t    = \TSSGGSModule\App\ClientArea\SslPages::texts();
            $menu = $secondarySidebar->addChild('tssggsSslSummary', [
                'label' => $t['summaryTitle'],
                'icon'  => 'fa-lock',
                'order' => 99,
            ]);
            unifiedSslLabelBlock($menu, $t['summaryTitle'], \TSSGGSModule\App\Libs\Support\Edition::brand());
            $order = 10;
            foreach(\TSSGGSModule\App\Libs\Certificates\ClientSslSummary::TYPES as $type)
            {
                $menu->addChild('tssggsSsl_' . $type, [
                    'label' => $t[$type],
                    'uri'   => 'index.php?m=TSSGGSModule&type=' . $type,
                    'badge' => (int)$counts[$type],
                    'order' => $order++,
                ]);
            }
            if(\TSSGGSModule\App\Libs\Support\GeneralSettings::enabled('certificatesByDomain'))
            {
                $menu->addChild('tssggsSsl_domains', [
                    'label' => $t['byDomain'],
                    'uri'   => 'index.php?m=TSSGGSModule&page=domains',
                    'order' => $order++,
                ]);
            }
            if(\TSSGGSModule\App\Libs\Support\GeneralSettings::enabled('sslTools'))
            {
                $menu->addChild('tssggsSsl_tools', [
                    'label' => $t['sslTools'],
                    'uri'   => 'index.php?m=TSSGGSModule&page=tools',
                    'order' => $order,
                ]);
            }
        }
        catch(\Throwable $e)
        {
        }
    }
}
