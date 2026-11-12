<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\Commissions as CommissionRules;
use ModulesGarden\TSSGGSModule\App\Libs\CsrfToken;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Commissions\Index\CommissionsContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;

/**
 * Per-client commissions: list, add / change (POST), delete (link with the admin token)
 */
class Commissions extends AbstractController implements AdminAreaInterface
{
    const SESSION = 'tssggs_commissions';

    public function index()
    {
        return Helper\view()->addElement(CommissionsContainer::class);
    }

    public function save()
    {
        return $this->run('saved', function() {
            CommissionRules::set((int)($_POST['client_id'] ?? 0), (int)($_POST['product_id'] ?? 0), str_replace(',', '.', trim((string)($_POST['commission'] ?? ''))));
        });
    }

    public function delete()
    {
        return $this->run('deleted', function() {
            CommissionRules::delete((int)($_REQUEST['id'] ?? 0));
        });
    }

    protected function run($notice, callable $action)
    {
        try
        {
            if(!CsrfToken::valid($_REQUEST['token'] ?? ''))
            {
                throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
            }
            $action();
            $_SESSION[self::SESSION]['notice'] = $notice;
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        return Helper\redirect('commissions', 'index');
    }
}
