<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;

/**
 * Renewal dialog of S/MIME and code signing certificates.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait GatewayRenewalActions
{
    /* ---------------- Renewal of S/MIME / code signing ---------------- */

    /**
     * Renew button and dialog of an S/MIME / code signing service page
     */
    private function gatewayRenewalVars($type, $serviceId, array $stored, $input, array $vars)
    {
        $vars['renewOpen']       = $type::renewalOpen($serviceId);
        $vars['renewSame']       = $type::canRenewWithSameDetails($stored);
        $vars['renewNeedsModel'] = $type === CodeSigningService::class && CodeSigningService::renewalNeedsTokenModel($stored);
        $vars['renewIsCs']       = $type === CodeSigningService::class;
        // document signing renews onto the token the client already has, so "same details" asks no model
        $vars['renewIsDs']       = $type === \ModulesGarden\TSSGGSModule\App\Libs\DocumentSigningService::class;
        // LEI: "same details" renews with the legal entity data and the signing authority unchanged
        $vars['renewIsLei']      = $type === \ModulesGarden\TSSGGSModule\App\Libs\LeiService::class;
        $vars['renewModels']     = CodeSigningService::TOKEN_MODELS;
        $vars['renewAgreement']  = CertificateService::agreement((int)$input['params']['pid']);
        $vars['csrfToken']       = $vars['csrfToken'] ?? \generate_token('plain');
        // Result of the Renew dialog (renewOrderHTML redirects back here)
        $vars['flashSuccess']    = $_SESSION['mg-success'] ?? '';
        $vars['flashError']      = $_SESSION['mg-error'] ?? '';
        unset($_SESSION['mg-success'], $_SESSION['mg-error']);

        return $vars;
    }

    /**
     * Renew dialog: keeps the client's choice (same details / new details) and creates the renewal invoice; the order
     * is placed when the invoice is paid (WHMCS Renew)
     */
    public function renewOrderHTML($input, $vars = [])
    {
        global $CONFIG;

        $serviceId = (int)$input['params']['serviceid'];
        try
        {
            $this->requireCsrfToken();
            $type = \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::typeOfService($serviceId);
            if(!$type || !$type::renewalOpen($serviceId))
            {
                throw new \Exception(Lang::absoluteT('renewNotAvailable'));
            }

            $stored = $type::stored($serviceId);
            $same   = !empty($input['renew_same']) && $type::canRenewWithSameDetails($stored);
            $extra  = [];
            if($same && $type === CodeSigningService::class && CodeSigningService::renewalNeedsTokenModel($stored))
            {
                $model = strtoupper(trim((string)($input['renew_model'] ?? '')));
                if(!isset(CodeSigningService::TOKEN_MODELS[$model]))
                {
                    throw new \Exception(Lang::absoluteT('csTokenModelRequired'));
                }
                $extra['model'] = $model;
            }
            if($same && CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
            {
                throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
            }
            $type::setRenewChoice($serviceId, $same, $extra);

            list($invoiceId, $created) = (new InvoiceRenew())->getOrCreateInvoice($serviceId);
            $_SESSION['mg-success'] = Lang::absoluteT($created ? 'renew_invoice_generated' : 'renew_exists') . ' <a target="_blank" href="' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . (int)$invoiceId . '">' . Lang::absoluteT('Click here to view the invoice') . '</a>';
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
        }
        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }
}
