<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Support\ClientText;

/**
 * Service actions on an SSL certificate: install on cPanel, renew, cancel.
 * Part of SslCertificatePages (used there as a trait).
 */
trait SslServiceActions
{
    /**
     * "Install on server" - installs the issued certificate on the client's cPanel account
     */
    public function installCertificateHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();
            CertificateService::requireUsable($serviceId);
            if(!$this->acquireServiceLock($serviceId))
            {
                throw new \Exception(Lang::absoluteT('sslConfigInProgress'));
            }
            try
            {
                CertificateService::installOnCpanel($serviceId, isset($input['install_domain']) ? $input['install_domain'] : '', isset($input['private_key']) ? $input['private_key'] : '');
            }
            finally
            {
                $this->releaseServiceLock($serviceId);
            }
            $_SESSION['mg-success'] = Lang::absoluteT('install_success');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = Lang::absoluteT('install_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function renewHTML($input, $vars = [])
    {
        global $CONFIG;

        try
        {
            $this->requireCsrfToken();

            list($invoiceId, $created) = (new InvoiceRenew())->getOrCreateInvoice((int)$input['id']);

            $_SESSION['mg-success'] = Lang::absoluteT($created ? 'renew_invoice_generated' : 'renew_exists') . ' <a target="_blank" href="' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . (int)$invoiceId . '">' . Lang::absoluteT('Click here to view the invoice') . '</a>';
            redir('action=productdetails&id=' . $input['id'], 'clientarea.php');

        }
        catch(\Exception $e)
        {

            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . $input['id'], 'clientarea.php');

        }


    }

    public function cancelHTML($input, $vars = [])
    {
        try
        {
            $this->requireCsrfToken();
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
        // the same cancellation as Terminate / admin "Cancel Order" (vendor order, stored statuses, WHMCS SSL order,
        // AutoInstall tasks), then the service itself
        try
        {
            if(!CertificateService::serviceOpen((int)$input['id']))
            {
                throw new \Exception(ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.'));
            }
            CertificateService::cancel((int)$input['id'], 'Cancelled by customer');
            Service::where('id', (int)$input['id'])->update(['domainstatus' => 'Cancelled']);
            // a product addon's certificate: the addon is billed no more either (M-14)
            \ModulesGarden\TSSGGSModule\App\Libs\Certificates\AddonCertificates::followEnd((int)$input['id'], 'Cancelled');
            CertificateService::forgetPrivateKeys((int)$input['id']);
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
        }

        redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
    }
}
