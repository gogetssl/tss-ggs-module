<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\CsrfToken;
use ModulesGarden\TSSGGSModule\App\Libs\WhmcsModuleLog;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\HomeContainer;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\SettingsContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;

class Settings extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(SettingsContainer::class);
    }

    /** Settings > Logs: the "Turn on in WHMCS" button of the WHMCS Module Log warning (POST with the admin token) */
    public function enableWhmcsModuleLog()
    {
        try
        {
            if(($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !CsrfToken::valid($_POST['token'] ?? ''))
            {
                throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
            }
            WhmcsModuleLog::enable();
            $_SESSION[WhmcsModuleLog::SESSION] = ['ok' => true];
        }
        catch(\Throwable $e)
        {
            $_SESSION[WhmcsModuleLog::SESSION] = ['error' => $e->getMessage()];
        }

        header('Location: addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=logsSettings');
        exit;
    }
}
