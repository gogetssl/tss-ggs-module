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

        if(isset($request['page']) && $request['page'] === 'tools' && GeneralSettings::enabled('sslTools'))
        {
            return self::tools();
        }

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
     * "SSL Tools": CSR / certificate decoder, key match, converter, CSR generator, SSL checker (see SslTools). Every
     * action is a POST with the WHMCS token; a conversion answers with the file.
     */
    protected static function tools()
    {
        $vars = [
            'moduleUrl' => self::MODULE_URL,
            'token'     => function_exists('generate_token') ? \generate_token('plain') : '',
            'form'      => ['action' => '', 'host' => '', 'port' => 443, 'bits' => 2048],
            'result'    => null,
            'error'     => '',
            'active'    => '',
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $action           = isset($_POST['action']) ? (string)$_POST['action'] : '';
            $vars['active']   = $action;
            $vars['form']     = array_merge($vars['form'], array_map('strval', array_filter($_POST, 'is_scalar')));
            try
            {
                if(!\ModulesGarden\TSSGGSModule\App\Libs\CsrfToken::valid(isset($_POST['token']) ? $_POST['token'] : ''))
                {
                    throw new \Exception('The page expired - reload it and try again.');
                }
                $tools = '\ModulesGarden\TSSGGSModule\App\Libs\SslTools';
                switch($action)
                {
                    case 'decodeCsr':
                        $vars['result'] = $tools::decodeCsr(isset($_POST['csr']) ? $_POST['csr'] : '');
                        break;
                    case 'decodeCert':
                        $vars['result'] = $tools::decodeCertificate(isset($_POST['certificate']) ? $_POST['certificate'] : '');
                        break;
                    case 'match':
                        $vars['result'] = $tools::matchKeys(isset($_POST['certificate']) ? $_POST['certificate'] : '', isset($_POST['csr']) ? $_POST['csr'] : '', isset($_POST['key']) ? $_POST['key'] : '');
                        break;
                    case 'generate':
                        $vars['result'] = $tools::generate([
                            'commonName' => isset($_POST['commonName']) ? $_POST['commonName'] : '', 'organization' => isset($_POST['organization']) ? $_POST['organization'] : '',
                            'unit' => isset($_POST['unit']) ? $_POST['unit'] : '', 'city' => isset($_POST['city']) ? $_POST['city'] : '', 'state' => isset($_POST['state']) ? $_POST['state'] : '',
                            'country' => isset($_POST['country']) ? $_POST['country'] : '', 'email' => isset($_POST['email']) ? $_POST['email'] : '',
                        ], isset($_POST['bits']) ? $_POST['bits'] : 2048);
                        break;
                    case 'check':
                        $vars['result'] = $tools::check(isset($_POST['host']) ? $_POST['host'] : '', isset($_POST['port']) ? $_POST['port'] : 443);
                        break;
                    case 'convert':
                        $mode = isset($_POST['mode']) ? (string)$_POST['mode'] : '';
                        $file = isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['size'] < 1048576 ? file_get_contents($_FILES['file']['tmp_name']) : '';
                        $certificate = in_array($mode, ['der2pem', 'pfx2pem'], true) ? $file : (isset($_POST['certificate']) ? $_POST['certificate'] : '');
                        $converted   = $tools::convert($mode, $certificate, isset($_POST['key']) ? $_POST['key'] : '', isset($_POST['password']) ? $_POST['password'] : '', isset($_POST['chain']) ? $_POST['chain'] : '');
                        header('Content-Type: ' . $converted['mime']);
                        header('Content-Disposition: attachment; filename="' . $converted['filename'] . '"');
                        header('X-Content-Type-Options: nosniff');
                        echo $converted['content'];
                        exit;
                    default:
                        throw new \Exception('Choose a tool.');
                }
            }
            catch(\Exception $e)
            {
                $vars['error'] = $e->getMessage();
            }
            // the private key / password fields are never sent back to the page
            unset($vars['form']['key'], $vars['form']['password'], $vars['form']['token']);
        }

        return [
            'pagetitle'    => 'SSL Tools',
            'breadcrumb'   => [self::MODULE_URL . '&page=tools' => 'SSL Tools'],
            'templatefile' => 'templates/clientarea/tools',
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
