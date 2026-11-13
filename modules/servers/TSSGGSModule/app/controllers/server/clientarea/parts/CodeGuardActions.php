<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\TSSGGSModule\App\Libs\CodeGuardService;
use WHMCS\Database\Capsule;

/**
 * CodeGuard licences: the service page, the way into the CodeGuard dashboard and the contact of the licence.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait CodeGuardActions
{
    /**
     * CodeGuard service page. There is no order form: the licence is created with the service, so the page shows the
     * licence and the button into the dashboard, and offers to order again only if the first attempt failed.
     */
    private function indexCodeGuardHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'codeGuardNotInitialized'));
        }

        $vars = $this->codeGuardBaseVars($input, $vars);

        $stored = CodeGuardService::stored($serviceId);
        $vars['refreshError'] = '';
        if(CodeGuardService::refreshDue($stored))
        {
            try
            {
                $stored = CodeGuardService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? ''));
        $tones  = ['active' => 'ok', 'suspended' => 'warn', 'pending' => 'wait', 'cancelled' => 'off', 'refunded' => 'off', 'expired' => 'off'];
        $period = CodeGuardService::period($serviceId);

        $vars['codeGuardOrdered']   = !empty($stored['itemId']);
        $vars['codeGuardStatus']    = $status;
        $vars['codeGuardTone']      = $tones[$status] ?? 'wait';
        $vars['codeGuardOrderId']   = (int)($stored['orderId'] ?? 0);
        $vars['codeGuardLicenseId'] = (int)($stored['itemId'] ?? 0);
        $vars['codeGuardVendorId']  = (string)($stored['vendorOrderId'] ?? '');
        $vars['codeGuardContact']   = (array)($stored['contact'] ?? []);
        $vars['codeGuardStart']     = $period ? $this->codeGuardDate($period['start']) : '';
        $vars['codeGuardEnd']       = $period ? $this->codeGuardDate($period['end']) : '';
        $vars['codeGuardTerm']      = (int)($stored['term'] ?? 0);
        // the figures of the licence (quotas, backups): shown only for what the gateway actually fills in
        $vars['codeGuardLicense']   = $this->codeGuardLicenseRows(CodeGuardService::licenseInfo($serviceId));
        // the licence can be opened and changed while it is active or suspended, not once it is gone
        $vars['codeGuardUsable']    = $vars['codeGuardOrdered'] && !in_array($status, ['cancelled', 'refunded', 'expired'], true);

        return ['tpl' => 'home_codeguard', 'vars' => $vars];
    }

    /**
     * One-time link into the CodeGuard dashboard: asked for on every click, never stored, and opened by the browser.
     */
    public function openCodeGuardDashboardJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!CodeGuardService::isCodeGuardWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'codeGuardNotCodeGuardProduct'));
        }

        return ['success' => true, 'link' => CodeGuardService::ssoLink($serviceId), 'message' => Lang::absoluteT('serverCA', 'home', 'codeGuardDashboardOpened')];
    }

    public function refreshCodeGuardDetailsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!CodeGuardService::isOrdered($serviceId))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'codeGuardNotOrderedYet'));
        }
        CodeGuardService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('serverCA', 'home', 'codeGuardDetailsRefreshed')];
    }

    /**
     * Changes the administrator contact of the licence (name and e-mail address CodeGuard writes to).
     */
    public function updateCodeGuardContactJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!CodeGuardService::isCodeGuardWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'codeGuardNotCodeGuardProduct'));
        }

        CodeGuardService::editContact($serviceId, [
            'first_name' => trim((string)($input['cg_first_name'] ?? '')),
            'last_name'  => trim((string)($input['cg_last_name'] ?? '')),
            'email'      => trim((string)($input['cg_email'] ?? '')),
        ]);

        return ['success' => true, 'message' => Lang::absoluteT('serverCA', 'home', 'codeGuardContactUpdated')];
    }

    /**
     * Orders the licence after the order placed with the service failed (the page offers this only then).
     */
    public function createCodeGuardJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        // requireOrderableService takes the per-service lock on $input['id'], so it is released on the same value
        $serviceId = (int)$input['id'];
        $this->requireOrderableService($input, CodeGuardService::isCodeGuardWhmcsProduct((int)$input['params']['pid']));
        try
        {
            CodeGuardService::create($serviceId);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => Lang::absoluteT('serverCA', 'home', 'codeGuardOrdered')];
    }

    /**
     * The figures of the licence as label / value rows, leaving out what the gateway does not fill in
     */
    private function codeGuardLicenseRows(array $license)
    {
        $rows = [];
        foreach($license as $key => $value)
        {
            if($value === null || $value === '' || !is_scalar($value))
            {
                continue;
            }
            $label  = Lang::absoluteT('serverCA', 'home', 'codeGuardLicense_' . $key);
            $rows[] = [
                'label' => (!is_string($label) || $label === '' || strpos($label, 'codeGuardLicense_') !== false) ? ucfirst(str_replace('_', ' ', (string)$key)) : $label,
                'value' => is_bool($value) ? ($value ? 'Yes' : 'No') : (string)$value,
            ];
        }

        return $rows;
    }

    private function codeGuardBaseVars($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $service   = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        $product   = Product::find((int)$input['params']['pid']);
        $currency  = $service ? \getCurrency((int)$service->userid) : null;

        $vars['allOk']       = true;
        $vars['serviceid']   = $serviceId;
        $vars['assetsURL']   = Server::I()->getAssetsURL();
        $vars['csrfToken']   = \generate_token('plain');
        $vars['productName'] = $product ? $product->name : '';
        $vars['billing']     = $service ? [
            'registration' => $this->codeGuardDate($service->regdate),
            'nextDue'      => $this->codeGuardDate($service->nextduedate),
            'amount'       => \formatCurrency($service->amount, $currency ? $currency['id'] : null),
            'cycle'        => $service->billingcycle,
        ] : [];

        return $vars;
    }

    /** 'November 13, 2026' (the client area date format of the module) */
    private function codeGuardDate($value)
    {
        $time = $value && strpos((string)$value, '0000-00-00') !== 0 ? strtotime((string)$value) : false;

        return $time ? date('F j, Y', $time) : '';
    }
}
