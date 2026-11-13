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
        AisHelpers::ensureColumns();
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
        $vars['aisInstallMethod']   = $subscription && $subscription->install_method === AisHelpers::METHOD_ACME ? AisHelpers::METHOD_ACME : AisHelpers::METHOD_AUTOINSTALL;
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

        $api = CertificateService::api($serviceId);

        // TheSSLStore product ids are codes (rapidssl_automate), GoGetSSL ids are numbers
        $apiProductId = isset($input['params']['configoption2']) ? \ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi::productId($input['params']['configoption2']) : '';
        if($apiProductId === '' || $apiProductId === 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingProductId'));
        }

        $method = isset($input['install_method']) && $input['install_method'] === AisHelpers::METHOD_ACME ? AisHelpers::METHOD_ACME : AisHelpers::METHOD_AUTOINSTALL;
        $response = $api->createAis([
            'product'        => ['id' => $apiProductId],
            'domains'        => [$domain],
            'install_method' => $method,
            'contacts'       => $this->aisContacts($input['params'], (string)$input['params']['configoption1']),
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
        AisHelpers::ensureColumns();
        $subscription->install_method = $method;
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
        if(in_array(strtolower((string)$subscription->status), ['cancelled', 'terminated'], true))
        {
            throw new \Exception(Lang::absoluteT('subscriptionAlreadyCancelled'));
        }

        // without the vendor's item ID nothing can be cancelled there: never report a cancellation
        // that did not happen (the vendor would keep billing the subscription)
        if((int)$subscription->ais_item_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('sslConfigOrderFailedCancel'));
        }
        // within the refund window the vendor refunds the order and the client is credited
        $refundable = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::orderRefundable($serviceId);
        CertificateService::api($serviceId)->cancelItem((int)$subscription->ais_item_id, 'Cancelled by client', 'ais', $refundable);

        $subscription->status       = 'cancelled';
        $subscription->auto_renew   = 0;
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Cancelled']);

        $credited = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::refundOnCancel($serviceId, 'Subscription cancelled by the client');
        $message  = Lang::absoluteT('aisSubscriptionCancelled');
        if($credited > 0)
        {
            $message = sprintf(Lang::absoluteT('acme_subscription_cancelled_and_credits_refunded'), number_format($credited, 2, '.', ''));
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Switches between the AutoInstall SSL agent and the client's own ACME client
     */
    public function changeAisInstallMethodJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $method       = (string)($input['install_method'] ?? '');
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!in_array($method, [AisHelpers::METHOD_AUTOINSTALL, AisHelpers::METHOD_ACME], true))
        {
            throw new \Exception(Lang::absoluteT('aisInvalidInstallMethod'));
        }
        if(!$subscription || (int)$subscription->ais_item_id <= 0 || in_array(strtolower((string)$subscription->status), ['cancelled', 'terminated', 'expired'], true))
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('requestInProgress'));
        }

        CertificateService::api($serviceId)->changeAisInstallMethod((int)$subscription->ais_item_id, $method);
        AisHelpers::ensureColumns();
        $subscription->install_method = $method;
        $subscription->save();
        CertificateService::event($serviceId, 'info', '[Subscription] Install method changed by the client: ' . $method);

        try
        {
            // the ACME account (switched to ACME) or the AutoInstall SSL links (switched back)
            AisHelpers::refreshAisDetails($serviceId);
        }
        catch(\Exception $e)
        {
            // read again on the next page load
        }

        return ['success' => true, 'message' => Lang::absoluteT('aisInstallMethodChanged')];
    }

    /** contacts of a subscription order: the client (GoGetSSL takes the e-mail only, TheSSLStore the full contact) */
    private function aisContacts(array $params, $vendor)
    {
        $c       = isset($params['clientsdetails']) && is_array($params['clientsdetails']) ? $params['clientsdetails'] : [];
        $contact = ['email' => (string)($c['email'] ?? '')];
        if(strtoupper($vendor) === 'TSS')
        {
            $contact = [
                'first_name' => (string)($c['firstname'] ?? ''),
                'last_name'  => (string)($c['lastname'] ?? ''),
                'email'      => (string)($c['email'] ?? ''),
                'phone'      => (string)($c['phonenumberformatted'] ?? ($c['phonenumber'] ?? '')),
            ];
        }

        return ['primary' => $contact, 'technical' => $contact];
    }
}
