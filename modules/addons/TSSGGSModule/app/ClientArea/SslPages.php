<?php

namespace ModulesGarden\TSSGGSModule\App\ClientArea;

use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\ClientSslSummary;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;

/**
 * Client area pages of the addon (index.php?m=TSSGGSModule): the client's SSL certificates filtered like the
 * summary panel, and "SSL Certificates by Domain". Plain Smarty templates in templates/clientarea/.
 */
class SslPages
{
    const MODULE_URL = 'index.php?m=TSSGGSModule';

    public static function render(array $request)
    {
        self::bootLang();

        $userId  = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
        $summary = new ClientSslSummary($userId);
        $page    = isset($request['page']) && $request['page'] === 'domains' && GeneralSettings::enabled('certificatesByDomain') ? 'domains' : 'certificates';

        $vars = [
            'moduleUrl' => self::MODULE_URL,
            'counts'    => $summary->counts(),
            'page'      => $page,
            'byDomain'  => GeneralSettings::enabled('certificatesByDomain'),
            't'         => self::texts(),
        ];

        if($page === 'domains')
        {
            $search = isset($request['search']) ? strtolower(trim((string)$request['search'])) : '';
            $status = isset($request['status']) ? (string)$request['status'] : '';
            $rows   = [];
            foreach($summary->domains() as $domain => $certificates)
            {
                if($search !== '' && strpos($domain, $search) === false)
                {
                    continue;
                }
                $current = $certificates[0];
                if($status === 'active' && $current['status'] !== 'active' || $status === 'inactive' && $current['status'] === 'active')
                {
                    continue;
                }
                $rows[] = ['domain' => $domain, 'current' => $current, 'history' => array_slice($certificates, 1)];
            }

            $vars['domains'] = $rows;
            $vars['search']  = $search;
            $vars['status']  = $status;
            $title           = $vars['t']['domainsTitle'];
        }
        else
        {
            $type             = isset($request['type']) && in_array($request['type'], ClientSslSummary::TYPES, true) ? $request['type'] : 'total';
            $vars['type']     = $type;
            $vars['services'] = $summary->filter($type);
            $title            = $vars['t']['certificatesTitle'];
        }

        return [
            'pagetitle'    => $title,
            'breadcrumb'   => [self::MODULE_URL => $title],
            'templatefile' => 'templates/clientarea/' . ($page === 'domains' ? 'domains' : 'certificates'),
            'requirelogin' => true,
            'forcessl'     => false,
            'vars'         => $vars,
        ];
    }

    /**
     * Texts of the client pages from the server module language files (incl. langs/overrides)
     */
    public static function texts()
    {
        self::bootLang();

        $keys  = ['certificatesTitle', 'domainsTitle', 'summaryTitle', 'viewAll', 'total', 'unpaid', 'processing', 'expires_soon',
                  'domain', 'product', 'status', 'validTill', 'nextDueDate', 'manage', 'payInvoice', 'noCertificates', 'search',
                  'searchPlaceholder', 'allStatuses', 'activeOnly', 'inactiveOnly', 'history', 'ordered', 'validFrom', 'noDomains',
                  'byDomain', 'listView', 'certificate', 'status_active', 'status_processing', 'status_pending', 'status_cancelled',
                  'status_expired', 'status_rejected', 'status_revoked', 'status_awaiting'];
        $texts = [];
        foreach($keys as $key)
        {
            $texts[$key] = Lang::absoluteT('clientPages', $key);
        }

        return $texts;
    }

    protected static function bootLang()
    {
        if(!class_exists('\ModulesGarden\Servers\TSSGGSModule\Loader', false))
        {
            require_once dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'TSSGGSModule' . DIRECTORY_SEPARATOR . 'Loader.php';
            new \ModulesGarden\Servers\TSSGGSModule\Loader();
        }
        if(!defined('DS'))
        {
            define('DS', DIRECTORY_SEPARATOR);
        }
        // The server module kernel loads its language files (english + client's language + langs/overrides)
        \ModulesGarden\Servers\TSSGGSModule\Server::I();
    }
}
