<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\AisHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use WHMCS\Database\Capsule;

/**
 * AutoInstall SSL subscriptions: pages, creation, auto-renewal and cancellation.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait AisActions
{
    private function indexAisHTML($input, $vars = [])
    {
        $serviceId    = (int)$input['params']['serviceid'];
        $userId       = (int)$input['params']['userid'];
        $sslService   = SSL::getByServiceId($serviceId);
        $product      = Product::find((int)$input['params']['pid']);
        $subscription = AisSubscription::getByServiceId($serviceId);

        // not active yet, or switched to ACME with an ACME account not active yet (the keys work only then)
        if($subscription && (int)$subscription->api_order_id > 0 && (strtolower((string)$subscription->status) !== 'active'
            || ((string)$subscription->eab_kid !== '' && strtolower((string)$subscription->account_status) !== 'active')))
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
        $vars['aisCanSwitch']       = AisHelpers::METHOD_SWITCH_AVAILABLE;
        $vars['configurationStatus'] = $sslService ? $sslService->status : null;
        $vars['configurationURL']   = 'clientarea.php?action=productdetails&id=' . $serviceId . '&aisconfig=1';
        // auto renewal stopped: the subscription ends with its period, a cancel is refused (GGS-3) - the end date is shown
        $vars['endsAtPeriodEnd']     = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionState::endsAtPeriodEnd($subscription);
        $vars['endsAtPeriodEndNote'] = $vars['endsAtPeriodEnd'] ? str_replace(':date:', \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionState::periodEndText($subscription), Lang::absoluteT('serverCA', 'home', 'acmeEndsAtPeriodEnd')) : '';
        $vars['acmeKeysUsable']      = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionState::keysUsable($subscription);
        $vars['renewalUnpaid']       = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionState::renewalUnpaid($subscription);

        if($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return ['tpl' => 'home_ais_awaiting_configuration', 'vars' => $vars];
        }

        return ['tpl' => 'home_ais', 'vars' => $vars];
    }

    private function aisConfigurationHTML($input, $vars = [])
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
        $vars['aisCanSwitch']      = AisHelpers::METHOD_ON_ORDER;

        return ['tpl' => 'home_ais_configuration', 'vars' => $vars];
    }

    public function createAisJSON($input, $vars = [])
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
        $apiProductId = isset($input['params']['configoption2']) ? \ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi::productId($input['params']['configoption2']) : '';
        if($apiProductId === '' || $apiProductId === 0)
        {
            throw new \Exception(Lang::absoluteT('aisMissingProductId'));
        }

        $order = [
            'product'  => ['id' => $apiProductId],
            'domains'  => [$domain],
            'contacts' => $this->aisContacts($input['params'], (string)$input['params']['configoption1']),
        ];
        // the method is sent once the gateway takes it on a new order (see AisHelpers::METHOD_ON_ORDER)
        if(AisHelpers::METHOD_ON_ORDER && isset($input['install_method']) && $input['install_method'] === AisHelpers::METHOD_ACME)
        {
            $order['install_method'] = AisHelpers::METHOD_ACME;
        }
        $response = $api->createAis($order);

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

    public function refreshAisDetailsJSON($input, $vars = [])
    {
        $serviceId = (int)$input['id'];
        AisHelpers::refreshAisDetails($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('aisDetailsRefreshed')];
    }

    public function stopAisAutoRenewalJSON($input, $vars = [])
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

    public function cancelAisSubscriptionJSON($input, $vars = [])
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
        if(\ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionState::endsAtPeriodEnd($subscription))
        {
            throw new \Exception(Lang::absoluteT('acmeCancelledAtPeriodEnd'));
        }

        // without the vendor's item ID nothing can be cancelled there: never report a cancellation
        // that did not happen (the vendor would keep billing the subscription)
        if((int)$subscription->ais_item_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('sslConfigOrderFailedCancel'));
        }
        // within the refund window the vendor refunds the order and the client is credited
        $refundable = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::orderRefundable($serviceId);
        CertificateService::api($serviceId)->cancelItem((int)$subscription->ais_item_id, 'Cancelled by client', 'ais', $refundable);

        $subscription->status       = 'cancelled';
        $subscription->auto_renew   = 0;
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update(['domainstatus' => 'Cancelled']);

        $credited = \ModulesGarden\TSSGGSModule\App\Libs\Subscriptions\SubscriptionBilling::refundOnCancel($serviceId, 'Subscription cancelled by the client');
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
    public function changeAisInstallMethodJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $method       = (string)($input['install_method'] ?? '');
        $subscription = AisSubscription::getByServiceId($serviceId);

        if(!AisHelpers::METHOD_SWITCH_AVAILABLE || !in_array($method, [AisHelpers::METHOD_AUTOINSTALL, AisHelpers::METHOD_ACME], true))
        {
            throw new \Exception(Lang::absoluteT('aisInvalidInstallMethod'));
        }
        if(!$subscription || (int)$subscription->ais_item_id <= 0 || in_array(strtolower((string)$subscription->status), ['cancelled', 'terminated', 'expired'], true))
        {
            throw new \Exception(Lang::absoluteT('aisSubscriptionNotCreatedYet'));
        }
        if($subscription->install_method === $method)
        {
            return ['success' => true, 'message' => Lang::absoluteT('aisInstallMethodChanged')];
        }
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('requestInProgress'));
        }
        try
        {
            CertificateService::api($serviceId)->changeAisInstallMethod((int)$subscription->ais_item_id, $method);
            $subscription->install_method = $method;
            // the ACME account of an earlier switch is not this one: the details bring the new account (GoGetSSL: once it is
            // registered, a few minutes later - the page shows it as being set up until then)
            $subscription->acme_account_id = null;
            $subscription->eab_kid         = null;
            $subscription->eab_hmac_key    = null;
            $subscription->server_url      = null;
            $subscription->save();
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }
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

    /**
     * Contacts of a subscription order: the client's e-mail as primary and technical contact. Both vendors take the
     * e-mail only - any other contact field is refused ("This field was not expected", TheSSLStore sandbox 2026-11-17).
     * $vendor is kept for the callers.
     */
    private function aisContacts(array $params, $vendor)
    {
        $c       = isset($params['clientsdetails']) && is_array($params['clientsdetails']) ? $params['clientsdetails'] : [];
        $contact = ['email' => (string)($c['email'] ?? '')];

        return ['primary' => $contact, 'technical' => $contact];
    }
}
