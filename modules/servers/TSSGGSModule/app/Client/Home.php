<?php

namespace TSSGGSModule\Server\app\Client;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\Order;
use TSSGGSModule\Server\app\models\whmcs\Product;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\Text;
use TSSGGSModule\App\Libs\Subscriptions\AcmeHelpers;
use TSSGGSModule\App\Libs\Subscriptions\AisHelpers;
use TSSGGSModule\App\Libs\ProductLines\SmimeService;
use TSSGGSModule\App\Libs\ProductLines\CodeSigningService;
use TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

class Home
{
    // the pages and actions by product type
    use Parts\SslCertificatePages;
    use Parts\AcmeActions;
    use Parts\CodeSigningActions;
    use Parts\CodeGuardActions;
    use Parts\DocumentSigningActions;
    use Parts\PciActions;
    use Parts\LeiActions;
    use Parts\GatewayRenewalActions;
    use Parts\SmimeActions;
    use Parts\AisActions;
    use Parts\SslConfigurationActions;


    function indexHTML($input, $vars = [])
    {
        $vars['csrfToken'] = \generate_token('plain');

        try
        {
            $serviceId = $input['id'];
            $ssl       = SSL::getByServiceId($serviceId);

            $service = \WHMCS\Service\Service::find($serviceId);

            if(SmimeService::isSmimeWhmcsProduct($service->packageid))
            {
                return $this->indexSmimeHTML($input, $vars);
            }

            if(CodeSigningService::isCodeSigningWhmcsProduct($service->packageid))
            {
                return $this->indexCodeSigningHTML($input, $vars);
            }

            if(\TSSGGSModule\App\Libs\ProductLines\CodeGuardService::isCodeGuardWhmcsProduct($service->packageid))
            {
                return $this->indexCodeGuardHTML($input, $vars);
            }

            if(\TSSGGSModule\App\Libs\ProductLines\DocumentSigningService::isDocumentSigningWhmcsProduct($service->packageid))
            {
                return $this->indexDocumentSigningHTML($input, $vars);
            }

            if(\TSSGGSModule\App\Libs\ProductLines\PciService::isPciWhmcsProduct($service->packageid))
            {
                return $this->indexPciHTML($input, $vars);
            }
            if(\TSSGGSModule\App\Libs\ProductLines\LeiService::isLeiWhmcsProduct($service->packageid))
            {
                return $this->indexLeiHTML($input, $vars);
            }

            if(AisHelpers::isAisWhmcsProduct($service->packageid))
            {
                if(isset($_GET['aisconfig']) && (string)$_GET['aisconfig'] === '1')
                {
                    return $this->aisConfigurationHTML($input, $vars);
                }
                return $this->indexAisHTML($input, $vars);
            }

            if(AcmeHelpers::isAcmeWhmcsProduct($service->packageid))
            {
                if(isset($_GET['acmeconfig']) && (string)$_GET['acmeconfig'] === '1')
                {
                    return $this->acmeConfigurationHTML($input, $vars);
                }
                return $this->indexAcmeHTML($input, $vars);
            }

            if(isset($ssl->status) && $ssl->status == 'Awaiting Configuration')
            {
                if(isset($_GET['configssl']) && (string)$_GET['configssl'] === '1')
                {
                    return $this->sslConfigurationHTML($input, $vars);
                }
                return $this->awaitingConfiguration($serviceId, $ssl);
            }

            if(isset($ssl->status) && $ssl->status == 'Configuration Submitted')
            {
                return $this->configurationSubmitted($input, $ssl);
            }

        }
        catch(\Exception $e)
        {

            return ['tpl' => 'error', 'vars' => ['error' => $e->getMessage()]];

        }

        return ['tpl' => 'home', 'vars' => $vars];

    }


    /**
     * State-changing client actions must be POSTed with the WHMCS CSRF token - WHMCS itself verifies the token
     * only on its own forms, so module actions reachable via GET would otherwise be open to CSRF.
     */
    /**
     * Actions that order at the vendor: only for the product type they belong to, an active (paid) service, and one
     * request at a time (a double click must not order twice)
     */
    private function requireOrderableService($input, $rightProduct)
    {
        if(!$rightProduct || ($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Text::get('smimeServiceNotActive'));
        }
        if(!$this->acquireServiceLock((int)$input['id']))
        {
            throw new \Exception(Text::get('requestInProgress'));
        }
    }

    /** An action of one product type called on a service of another */
    private function requireProduct($rightProduct)
    {
        if(!$rightProduct)
        {
            throw new \Exception(Text::get('smimeServiceNotActive'));
        }
    }

    /** One request at a time for the service (the lock is released when the request ends) */
    private function requireServiceLock($serviceId)
    {
        if(!$this->acquireServiceLock((int)$serviceId))
        {
            throw new \Exception(Text::get('requestInProgress'));
        }
    }

    private function requireCsrfToken()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !\TSSGGSModule\App\Libs\Support\CsrfToken::valid($_POST['token'] ?? ''))
        {
            throw new \Exception(Text::get('invalidCsrfToken'));
        }
    }

    /**
     * Per-service MySQL lock so a double submit cannot place two orders / create two subscriptions.
     * Returns false when another request holds the lock.
     */
    private function acquireServiceLock($serviceId)
    {
        $row = Capsule::select('SELECT GET_LOCK(?, 0) AS l', ['tssggs_service_' . (int)$serviceId]);

        return !empty($row) && (int)$row[0]->l === 1;
    }

    private function releaseServiceLock($serviceId)
    {
        Capsule::select('SELECT RELEASE_LOCK(?)', ['tssggs_service_' . (int)$serviceId]);
    }

    private function extractDomainsFromInput($domains)
    {
        $items = preg_split('/[\s,;]+/', trim((string)$domains));
        $items = array_filter(array_map('trim', $items));
        $items = array_values(array_unique(array_map('strtolower', $items)));

        return $items;
    }
}
