<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Domain;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\ProductConfigOptions;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepThree;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\Servers\TSSGGSModule\core\process\AbstractController;
use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AisHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateBundle;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Libs\SmimeService;
use ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService;
use ModulesGarden\TSSGGSModule\App\Libs\SslProductHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscriptionDomain;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;
use WHMCS\Billing\Tax;
use WHMCS\Database\Capsule;

/**
 * AutoInstall SSL subscriptions: pages, creation, auto-renewal and cancellation.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait AisActions
{
    private function indexAisHTML($input, $vars = array())
    {
        $serviceId    = (int)$input['params']['serviceid'];
        $userId       = (int)$input['params']['userid'];
        $sslService   = SSL::getByServiceId($serviceId);
        $product      = Product::find((int)$input['params']['pid']);
        $subscription = AisSubscription::getByServiceId($serviceId);

        if($subscription && (int)$subscription->api_order_id > 0 && strtolower((string)$subscription->status) !== 'active')
        {
            try
            {
                AisHelpers::refreshAisDetails($serviceId);
                $subscription = AisSubscription::getByServiceId($serviceId);
            }
            catch(\Exception $e)
            {
                \logActivity('TSSGGS: Unable to auto-refresh AIS details for service #' . $serviceId . '. Error: ' . $e->getMessage());
            }
        }

        $vars['allOk']              = true;
        $vars['serviceid']          = $serviceId;
        $vars['userid']             = $userId;
        $vars['assetsURL']          = Server::I()->getAssetsURL();
        $vars['isAisSubscription']  = true;
        $vars['productName']        = isset($product->name) ? $product->name : '';
        $vars['subscription']       = $subscription;
        $vars['aisAutoRenew']       = $subscription && isset($subscription->auto_renew) ? (int)$subscription->auto_renew : 1;
        $vars['configurationStatus'] = $sslService ? $sslService->status : null;
        $vars['configurationURL']   = 'clientarea.php?action=productdetails&id=' . $serviceId . '&aisconfig=1';

        if($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return ['tpl' => 'home_ais_awaiting_configuration', 'vars' => $vars];
        }

        return ['tpl' => 'home_ais', 'vars' => $vars];
    }

    private function aisConfigurationHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $userId    = (int)$input['params']['userid'];
        $product   = Product::find((int)$input['params']['pid']);
        $service   = \WHMCS\Service\Service::find($serviceId);

        $vars['allOk']             = true;
        $vars['serviceid']         = $serviceId;
        $vars['userid']            = $userId;
        $vars['assetsURL']         = Server::I()->getAssetsURL();
        $vars['isAisSubscription'] = true;
        $vars['productName']       = isset($product->name) ? $product->name : '';
        $vars['serviceDomain']     = $service ? $service->domain : '';

        return ['tpl' => 'home_ais_configuration', 'vars' => $vars];
    }

    public function createAisJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $this->requireOrderableService($input, AisHelpers::isAisWhmcsProduct((int)$input['params']['packageid']));
        $serviceId = (int)$input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';

        if(empty($domain))
        {
            throw new \Exception(Lang::absoluteT('aisDomainRequired'));
        }

        $subscription = AisSubscription::getByServiceId($serviceId);
        if($subscription && (int)$subscription->api_order_id > 0)
        {
            throw new \Exception(Lang::absoluteT('aisAlreadyCreated'));
        }

        $apis   = Helpers::getConfiguredApis();
        $vendor = $input['params']['configoption1'];
        $api    = $apis[$vendor];

        $client = \WHMCS\Service\Service::find($serviceId);

        $whmcsClient = Capsule::table('tblclients')
                              ->select(['email'])
                              ->where('id', (int)$input['params']['userid'])
                              ->first();

        $clientEmail  = $whmcsClient ? $whmcsClient->email : '';
        $apiProductId = isset($input['params']['configoption2']) ? (int)$input['params']['configoption2'] : 0;

        if($apiProductId <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingProductId'));
        }

        $response = $api->createAis([
            'product'  => ['id' => $apiProductId],
            'domains'  => [$domain],
            'contacts' => [
                'primary'   => ['email' => $clientEmail],
                'technical' => ['email' => $clientEmail],
            ],
        ]);

        $apiOrderId  = isset($response['order']['id'])     ? (int)$response['order']['id']     : 0;
        $orderStatus = isset($response['order']['status']) ? $response['order']['status']       : 'pending';
        $items       = isset($response['items']) && is_array($response['items']) ? $response['items'] : [];
        $firstItem   = isset($items[0]) ? (array)$items[0] : [];
        $aisItemId   = isset($firstItem['id']) ? (int)$firstItem['id'] : 0;

        if($apiOrderId <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingOrderId'));
        }

        if(!$subscription)
        {
            $subscription = new AisSubscription();
        }

        $subscription->service_id   = $serviceId;
        $subscription->client_id    = (int)$input['params']['userid'];
        $subscription->api_order_id = $apiOrderId;
        $subscription->ais_item_id  = $aisItemId > 0 ? $aisItemId : null;
        $subscription->status       = $orderStatus;
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domain' => $domain]);

        $sslOrder = SSL::getByServiceId($serviceId);
        if($sslOrder)
        {
            $sslOrder->setRemoteId($apiOrderId);
            $sslOrder->status = 'Configuration Submitted';
            $sslOrder->setSSLStatus($orderStatus);
            $sslOrder->save();
        }

        if($aisItemId > 0)
        {
            try
            {
                AisHelpers::refreshAisDetails($serviceId);
            }
            catch(\Exception $e)
            {
                // Non-fatal — links fetched on next page load
            }
        }

        return ['success' => true, 'message' => Lang::absoluteT('aisCreatedSuccessfully')];
    }

    public function refreshAisDetailsJSON($input, $vars = array())
    {
        $serviceId = (int)$input['id'];
        AisHelpers::refreshAisDetails($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('aisDetailsRefreshed')];
    }

    public function stopAisAutoRenewalJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        if((int)$subscription->ais_item_id > 0)
        {
            $api->disableAisAutoRenewal((int)$subscription->ais_item_id);
        }

        $subscription->auto_renew = 0;
        $subscription->save();

        return ['success' => true, 'message' => Lang::absoluteT('aisAutoRenewalDisabled')];
    }

    public function cancelAisSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }

        // without the vendor's item ID nothing can be cancelled there: never report a cancellation
        // that did not happen (the vendor would keep billing the subscription)
        $api = Helpers::getApiByServiceId($serviceId);
        if(!$api || (int)$subscription->ais_item_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('sslConfigOrderFailedCancel'));
        }
        $api->cancelAisSubscription((int)$subscription->ais_item_id, 'Cancelled by client');

        $subscription->status       = 'cancelled';
        $subscription->auto_renew   = 0;
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Cancelled']);

        return ['success' => true, 'message' => Lang::absoluteT('aisSubscriptionCancelled')];
    }
}
