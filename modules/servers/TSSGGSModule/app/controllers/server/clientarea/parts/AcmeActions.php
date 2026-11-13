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
 * ACME subscriptions: pages, domains, upgrades, cancellation and refund.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait AcmeActions
{
//ACME==================================================================================================================
    private function getAcmeDomainLimits(array $params)
    {
        $singleIncluded = isset($params['configoption7']) ? (int)$params['configoption7'] : 0;
        $singleBought   = isset($params['configoptions']['sans']) ? (int)$params['configoptions']['sans'] : 0;
        $singleLimit    = $singleIncluded + $singleBought;
        $wildcardIncluded = 0;
        $wildcardBought   = 0;

        if(Helpers::isWildcardEnabled((int)$params['packageid']))
        {
            $wildcardIncluded = isset($params['configoption8']) ? (int)$params['configoption8'] : 0;
            $wildcardBought   = isset($params['configoptions']['sans_wildcard']) ? (int)$params['configoptions']['sans_wildcard'] : 0;
            $wildcardLimit    = $wildcardIncluded + $wildcardBought;
        }
        else
        {
            $wildcardLimit = 0;
        }
        return [
            'single'   => $singleLimit,
            'wildcard' => $wildcardLimit,
            'singleFromOption' => $singleBought,
            'wildcardFromOption' => $wildcardBought,
            'singleIncluded' => $singleIncluded,
            'wildcardIncluded' => $wildcardIncluded,
        ];
    }

    private function validateAcmeDomain($domain, $domainType = 'single')
    {
        $domain = strtolower(trim((string)$domain));

        if($domainType === 'single' && strpos($domain, '*.') === 0)
        {
            throw new \Exception(Lang::absoluteT('single_domains_can_contain_only_regular_domains'));
        }

        if($domainType === 'wildcard')
        {
            if(strpos($domain, '*.') !== 0)
            {
                throw new \Exception(Lang::absoluteT('wildcard_domains_can_contain_only_wildcard_domains'));
            }
            $domain = substr($domain, 2);
        }

        if(Helpers::validateDomain($domain) !== true)
        {
            throw new \Exception(sprintf(Lang::absoluteT('invalid_domain'), $domain));
        }
    }

    private function getActiveAcmeDomainsCount($serviceId)
    {
        $activeDomains = AcmeSubscriptionDomain::where('service_id', $serviceId)->get();

        $single   = 0;
        $wildcard = 0;
        foreach($activeDomains as $domainRow)
        {
            if(isset($domainRow->domain_type) && strtolower((string)$domainRow->domain_type) === 'wildcard')
            {
                $wildcard++;
            }
            else
            {
                $single++;
            }
        }

        return [
            'single'   => $single,
            'wildcard' => $wildcard,
        ];
    }

    private function indexAcmeHTML($input, $vars = array())
    {
        $serviceId    = (int)$input['params']['serviceid'];
        $userId       = (int)$input['params']['userid'];
        $sslService   = SSL::getByServiceId($serviceId);
        $product      = Product::find((int)$input['params']['pid']);
        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if($subscription && (int)$subscription->api_order_id > 0 && strtolower((string)$subscription->status) !== 'active')
        {
            try
            {
                AcmeHelpers::refreshAcmeCertificateDetails($serviceId);
                $subscription = AcmeSubscription::where('service_id', $serviceId)->first();
            }
            catch(\Exception $e)
            {
                \logActivity('TSSGGS: Unable to auto-refresh ACME certificate details for service #' . (int)$serviceId . '. Error: ' . $e->getMessage());
            }
        }

        $domains = AcmeSubscriptionDomain::where('service_id', $serviceId)->get();

        $wildcardEnabled = Helpers::isWildcardEnabled($product->id);
        $limits          = $this->getAcmeDomainLimits($input['params']);
        //$singleBoughtSans       = isset($input['params']['configoptions']['sans']) ? (int)$input['params']['configoptions']['sans'] : 0;
        //$wildcardBoughtSans     = isset($input['params']['configoptions']['sans_wildcard']) ? (int)$input['params']['configoptions']['sans_wildcard'] : 0;
        $singleBoughtSans       = $limits['single'];
        $wildcardBoughtSans     = $limits['wildcard'];
        $activeCounts           = $this->getActiveAcmeDomainsCount($serviceId);
        $singleSansCurrent      = $activeCounts['single'];
        $wildcardSansCurrent    = $activeCounts['wildcard'];
        $totalSansCurrent       = $singleSansCurrent + $wildcardSansCurrent;
        $totalSansPurchased     = $singleBoughtSans + $wildcardBoughtSans;
        $availableSingleSlots   = max(0, $limits['single'] - $singleSansCurrent);
        $availableWildcardSlots = max(0, $limits['wildcard'] - $wildcardSansCurrent);
        $canAddDomains          = $availableSingleSlots > 0 || ($availableWildcardSlots > 0 && $wildcardEnabled);

        $vars['allOk']                  = true;
        $vars['serviceid']              = $serviceId;
        $vars['userid']                 = $userId;
        $vars['assetsURL']              = Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']     = true;
        $vars['productName']            = isset($product->name) ? $product->name : 'Sectigo ACME Certificate-as-a-Service';
        $vars['allowWildcard']          = $wildcardEnabled;
        $vars['subscription']           = $subscription;
        $vars['domains']                = $domains;
        $vars['singleSansCurrent']      = $singleSansCurrent;
        $vars['singleSansPurchased']    = $singleBoughtSans;
        $vars['wildcardSansCurrent']    = $wildcardSansCurrent;
        $vars['wildcardSansPurchased']  = $wildcardBoughtSans;
        $vars['totalSansCurrent']       = $totalSansCurrent;
        $vars['totalSansPurchased']     = $totalSansPurchased;
        $vars['availableSingleSlots']   = $availableSingleSlots;
        $vars['availableWildcardSlots'] = $availableWildcardSlots;
        $vars['canAddDomains']          = $canAddDomains;
        $vars['configurationStatus']    = $sslService ? $sslService->status : null;
        $vars['configurationURL']       = 'clientarea.php?action=productdetails&id=' . $serviceId . '&acmeconfig=1';

        if($vars['configurationStatus'] === 'Awaiting Configuration')
        {
            return array(
                'tpl'  => 'home_acme_awaiting_configuration',
                'vars' => $vars
            );
        }

        return array(
            'tpl'  => 'home_acme',
            'vars' => $vars
        );
    }

    private function acmeConfigurationHTML($input, $vars = array())
    {
        $whmcsProductId = (int)$input['params']['packageid'];
        $serviceId      = (int)$input['params']['serviceid'];
        $userId         = (int)$input['params']['userid'];

        $product = Capsule::table('tblproducts')
                          ->select(['name', 'configoption13'])
                          ->where('id', (int)$input['params']['pid'])
                          ->first();

        $singleLimit       = (int)$input['params']['configoption7'];
        $singleOptionCount = isset($input['params']['configoptions']['sans']) ? (int)$input['params']['configoptions']['sans'] : 0;
        $singleLimit       += $singleOptionCount;

        $wildcardLimit       = (int)$input['params']['configoption8'];
        $wildcardOptionCount = isset($input['params']['configoptions']['sans_wildcard']) ? (int)$input['params']['configoptions']['sans_wildcard'] : 0;
        $wildcardLimit       += $wildcardOptionCount;

        $vars['allOk']                = true;
        $vars['serviceid']            = $serviceId;
        $vars['userid']               = $userId;
        $vars['assetsURL']            = Server::I()->getAssetsURL();
        $vars['isAcmeSubscription']   = true;
        $vars['productName']          = isset($product->name) ? $product->name : 'Sectigo ACME Certificate-as-a-Service';
        $vars['allowWildcard']        = Helpers::isWildcardEnabled($whmcsProductId);
        $vars['singleDomainsLimit']   = $singleLimit;
        $vars['wildcardDomainsLimit'] = $wildcardLimit;

        return array(
            'tpl'  => 'home_acme_configuration',
            'vars' => $vars
        );
    }

    public function createSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $this->requireOrderableService($input, AcmeHelpers::isAcmeWhmcsProduct((int)$input['params']['packageid']));
        $whmcsProductId  = (int)$input['params']['packageid'];
        $serviceId       = (int)$input['id'];
        $singleDomains   = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if(empty($singleDomains) && empty($wildcardDomains))
        {
            $domains = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            if(empty($domains))
            {
                throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
            }

            foreach($domains as $domain)
            {
                if(strpos($domain, '*.') === 0)
                {
                    $domainsWithType[$domain] = 'wildcard';
                }
                else
                {
                    $domainsWithType[$domain] = 'single';
                }
            }
        }
        else
        {
            foreach($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if(empty($domainsWithType))
        {
            throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
        }

        $limits               = $this->getAcmeDomainLimits($input['params']);
        $singleDomainsCount   = 0;
        $wildcardDomainsCount = 0;
        foreach($domainsWithType as $type)
        {
            if($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if($singleDomainsCount > $limits['single'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_single_domains'), $limits['single']));
        }
        if($wildcardDomainsCount > 0 && $wildcardDomainsCount > $limits['wildcard'])
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_wildcard_domains'), $limits['wildcard']));
        }

        if(in_array('wildcard', $domainsWithType, true) && (!Helpers::isWildcardEnabled($whmcsProductId)))
        {
            throw new \Exception(Lang::absoluteT('wildcard_domains_are_not_enabled_for_this_product'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if($subscription && (int)$subscription->api_order_id > 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_is_already_created'));
        }

        foreach($domainsWithType as $domain => $type)
        {
            $this->validateAcmeDomain($domain, $type);
        }

        $domains = array_keys($domainsWithType);

        $apis   = Helpers::getConfiguredApis();
        $vendor = $input['params']['configoption1'];
        $api    = $apis[$vendor];

        // remote product ids can be codes (TSS: sectigo_caas_dv), not only numbers
        $acmeProductId = !empty($input['params']['configoption2']) ? $input['params']['configoption2'] : AcmeHelpers::getProductIds()[0];
        $acmeProductId = is_numeric($acmeProductId) ? (int)$acmeProductId : $acmeProductId;
        $order = [
            'product_id' => $acmeProductId,
            'domains'    => implode(',', $domains),
        ];
        if(strtoupper((string)$vendor) === 'TSS')
        {
            // TheSSLStore: a contact for the vendor's notices, and the domains the subscription is paid for
            $slots              = $this->getAcmeDomainLimits($input['params']);
            $order['contacts']  = $this->subscriptionContacts($input['params']);
            $order['reserved']  = ['single' => max(1, (int)$slots['single']), 'wildcard' => (int)$slots['wildcard']];
        }
        $response = $api->createAcmeSubscription($order);

        $apiOrderId   = isset($response['order_id']) ? (int)$response['order_id'] : (isset($response['id']) ? (int)$response['id'] : 0);
        $apiItems     = isset($response['items']) && is_array($response['items']) ? $response['items'] : array();
        $apiFirstItem = isset($apiItems[0]) && is_array($apiItems[0]) ? $apiItems[0] : array();
        $acmeId       = isset($apiFirstItem['id']) ? (int)$apiFirstItem['id'] : (isset($response['item_id']) ? (int)$response['item_id'] : 0);

        if($apiOrderId <= 0)
        {
            throw new \Exception(Lang::absoluteT('acme_api_did_not_return_subscription_order_id'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if(!$subscription)
        {
            $subscription = new AcmeSubscription();
        }

        $subscription->service_id      = $serviceId;
        $subscription->client_id       = (int)$input['params']['userid'];
        $subscription->api_order_id    = $apiOrderId;
        $subscription->acme_id         = $acmeId;
        $subscription->status          = isset($response['status']) ? $response['status'] : 'active';
        $subscription->acme_account_id = isset($response['acme_account_id']) ? (string)$response['acme_account_id'] : '';
        $subscription->eab_kid         = isset($response['eab_kid']) ? (string)$response['eab_kid'] : '';
        $subscription->eab_hmac_key    = isset($response['eab_hmac_key']) ? (string)$response['eab_hmac_key'] : '';
        $subscription->server_url      = isset($response['server_url']) ? (string)$response['server_url'] : '';
        $subscription->period_start    = isset($response['begin_date']) ? (string)$response['begin_date'] : '';
        $subscription->period_end      = isset($response['end_date']) ? (string)$response['end_date'] : '';
        $subscription->renewal_date    = isset($response['renewal_date']) ? $response['renewal_date'] : (isset($response['end_date']) ? $response['end_date'] : null);
        $subscription->auto_renew      = 1;
        $subscription->save();

        foreach($domainsWithType as $domain => $type)
        {
            $now                             = date('Y-m-d H:i:s');
            $subscriptionDomain              = new AcmeSubscriptionDomain();
            $subscriptionDomain->service_id  = (int)$serviceId;
            $subscriptionDomain->domain      = $domain;
            $subscriptionDomain->domain_type = $type;
            $subscriptionDomain->status      = 'added';
            $subscriptionDomain->added_at    = $now;
            $subscriptionDomain->created_at  = $now;
            $subscriptionDomain->updated_at  = $now;
            $subscriptionDomain->save();
        }

        $sslOrder = SSL::getByServiceId($serviceId);

        if($sslOrder)
        {
            $sslOrder->setRemoteId($apiOrderId);
            $sslOrder->status = 'Configuration Submitted';
            $sslOrder->setConfigdataKey('subscription', true);
            $sslOrder->setSSLStatus($subscription->status);
            if(!empty($subscription->period_start))
            {
                $sslOrder->setConfigdataKey('begin_date', $subscription->period_start);
            }
            if(!empty($subscription->period_end))
            {
                $sslOrder->setConfigdataKey('end_date', $subscription->period_end);
            }
            if(!empty($subscription->renewal_date))
            {
                $sslOrder->setConfigdataKey('renewal_date', $subscription->renewal_date);
            }
            $sslOrder->save();
        }

        return [
            'success' => true,
            'message' => 'Subscription configuration submitted successfully.',
        ];
    }

    public function buyMoreDomainsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $this->requireOrderableService($input, AcmeHelpers::isAcmeWhmcsProduct((int)$input['params']['packageid']));
        $serviceId       = (int)$input['id'];
        $singleDomains   = $this->extractDomainsFromInput(isset($input['single_domains']) ? $input['single_domains'] : '');
        $wildcardDomains = $this->extractDomainsFromInput(isset($input['wildcard_domains']) ? $input['wildcard_domains'] : '');
        $domainsWithType = [];

        if(empty($singleDomains) && empty($wildcardDomains))
        {
            $domains    = $this->extractDomainsFromInput(isset($input['domains']) ? $input['domains'] : '');
            $domainType = isset($input['domain_type']) && $input['domain_type'] === 'wildcard' ? 'wildcard' : 'single';

            foreach($domains as $domain)
            {
                $domainsWithType[$domain] = $domainType;
            }
        }
        else
        {
            foreach($singleDomains as $domain)
            {
                $domainsWithType[$domain] = 'single';
            }
            foreach($wildcardDomains as $domain)
            {
                $domainsWithType[$domain] = 'wildcard';
            }
        }

        if(empty($domainsWithType))
        {
            throw new \Exception(Lang::absoluteT('please_provide_at_least_one_domain'));
        }

        $subscription = AcmeSubscription::where('service_id', $serviceId)->first();

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        if(in_array('wildcard', $domainsWithType, true) && (!Helpers::isWildcardEnabled($input['params']['packageid'])))
        {
            throw new \Exception(Lang::absoluteT('wildcard_domains_are_not_enabled_for_this_product'));
        }

        $limits            = $this->getAcmeDomainLimits($input['params']);
        $activeCounts      = $this->getActiveAcmeDomainsCount($serviceId);
        $remainingSingle   = max(0, $limits['single'] - $activeCounts['single']);
        $remainingWildcard = max(0, $limits['wildcard'] - $activeCounts['wildcard']);

        $singleDomainsCount   = 0;
        $wildcardDomainsCount = 0;
        foreach($domainsWithType as $type)
        {
            if($type === 'wildcard')
            {
                $wildcardDomainsCount++;
            }
            else
            {
                $singleDomainsCount++;
            }
        }

        if($singleDomainsCount > $remainingSingle)
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_single_domains'), $limits['single']));
        }

        if($wildcardDomainsCount > $remainingWildcard)
        {
            throw new \Exception(sprintf(Lang::absoluteT('exceeded_limit_of_wildcard_domains'), $limits['wildcard']));
        }

        foreach($domainsWithType as $domain => $type)
        {
            $exist = AcmeSubscriptionDomain::where('service_id', $serviceId)->where('domain', $domain)->exists();
            if($exist)
            {
                throw new \Exception(sprintf(Lang::absoluteT('domain_is_already_added'), $domain));
            }

            $this->validateAcmeDomain($domain, $type);
        }

        $api = Helpers::getApiByServiceId($serviceId);

        $api->addAcmeDomains((int)$subscription->acme_id, [
            'domains' => implode(',', array_keys($domainsWithType)),
        ]);

        foreach($domainsWithType as $domain => $type)
        {
            $now                             = date('Y-m-d H:i:s');
            $subscriptionDomain              = new AcmeSubscriptionDomain();
            $subscriptionDomain->service_id  = (int)$serviceId;
            $subscriptionDomain->domain      = $domain;
            $subscriptionDomain->domain_type = $type;
            $subscriptionDomain->status      = 'added';
            $subscriptionDomain->added_at    = $now;
            $subscriptionDomain->created_at  = $now;
            $subscriptionDomain->updated_at  = $now;
            $subscriptionDomain->save();
        }

        return [
            'success' => true,
            'message' => 'Domains added successfully.',
        ];
    }

    public function upgradeConfigOptionsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $params    = $input['params'];
        $serviceId = (int)$input['id'];
        $service   = Service::find($serviceId);

        if(!$service)
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_service_not_found'));
        }

        $newSingle   = isset($input['new_sans_count']) ? (int)$input['new_sans_count'] : -1;
        $newWildcard = isset($input['new_sans_wildcard_count']) ? (int)$input['new_sans_wildcard_count'] : -1;

        if($newSingle < 0 && $newWildcard < 0)
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_no_value_provided'));
        }

        $domainLimits = $this->getAcmeDomainLimits($params);

        // a value that was not sent (e.g. a product without wildcard domains) stays as it is
        $hasSingle   = $newSingle >= 0;
        $hasWildcard = $newWildcard >= 0;
        $newSingle   -= $domainLimits['singleIncluded'];
        $newWildcard -= $domainLimits['wildcardIncluded'];

        if((!$hasSingle || $newSingle == $domainLimits['singleFromOption']) && (!$hasWildcard || $newWildcard == $domainLimits['wildcardFromOption']))
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_no_change_provided'));
        }

        // giving bought slots back is a refund: possible only within the refund window of the last slot purchase
        $reduces = ($hasSingle && $newSingle < $domainLimits['singleFromOption']) || ($hasWildcard && $newWildcard < $domainLimits['wildcardFromOption']);
        if($reduces && !\ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::canReduceSlots($serviceId))
        {
            throw new \Exception(sprintf(Lang::absoluteT('acme_downgrade_refund_window_passed'), \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::REFUND_DAYS));
        }


        // Block upgrade if any unpaid invoice exists for this service (the renewal invoice does not block: it follows the
        // slots until it is paid) or a purchase of domains waits for its payment
        $unpaidInvoice = Capsule::table('tblinvoices')
                                ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                                ->where('tblinvoiceitems.type', 'Hosting')
                                ->where('tblinvoiceitems.relid', $serviceId)
                                ->where('tblinvoiceitems.description', 'NOT LIKE', '%' . AcmeHelpers::RENEWAL_LABEL)
                                ->whereIn('tblinvoices.status', ['Unpaid', 'Overdue'])
                                ->first();

        if($unpaidInvoice || \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::pendingSlotPurchase($serviceId))
        {
            throw new \Exception(Lang::absoluteT('acme_upgrade_unpaid_invoice_exists'));
        }

        $activeCounts         = $this->getActiveAcmeDomainsCount($serviceId);
        $configOptionsPayload = [];

        if($newSingle >= 0)
        {
            if($newSingle < ($activeCounts['single'] - $domainLimits['singleIncluded']))
            {
                throw new \Exception(sprintf(
                                         Lang::absoluteT('acme_downgrade_not_enough_single_slots'),
                                         $activeCounts['single']
                                     ));
            }

            $singleConfigId = ProductConfigOptions::getOptionIdBySystemName($service->packageid, 'sans');
            if(!$singleConfigId)
            {
                throw new \Exception(Lang::absoluteT('acme_upgrade_config_option_not_found'));
            }
            $configOptionsPayload[$singleConfigId] = $newSingle;
        }
        elseif($hasSingle)
        {
            throw new \Exception(sprintf(
                                     Lang::absoluteT('acme_downgrade_not_enough_single_slots'),
                                     $activeCounts['single']
                                 ));
        }


        if($newWildcard >= 0)
        {
            if($newWildcard < ($activeCounts['wildcard'] - $domainLimits['wildcardIncluded']))
            {
                throw new \Exception(sprintf(
                                         Lang::absoluteT('acme_downgrade_not_enough_wildcard_slots'),
                                         $activeCounts['wildcard']
                                     ));
            }
            $wildcardConfigId = ProductConfigOptions::getOptionIdBySystemName($service->packageid, 'sans_wildcard');
            if(!$wildcardConfigId)
            {
                throw new \Exception(Lang::absoluteT('acme_upgrade_config_option_not_found'));
            }
            $configOptionsPayload[$wildcardConfigId] = $newWildcard;
        }
        elseif($hasWildcard)
        {
            throw new \Exception(sprintf(
                                     Lang::absoluteT('acme_downgrade_not_enough_wildcard_slots'),
                                     $activeCounts['wildcard']
                                 ));
        }

        // more slots: bought on the module's invoice (prorated to the renewal or at the yearly price, Settings > SSL);
        // fewer slots (within the refund window): given back through WHMCS
        $bought    = [];
        $current   = [];
        foreach(['single' => [$singleConfigId ?? 0, $domainLimits['singleFromOption']], 'wildcard' => [$wildcardConfigId ?? 0, $domainLimits['wildcardFromOption']]] as [$configId, $from])
        {
            if($configId)
            {
                $current[$configId] = (int)$from;
            }
        }
        $reduce = [];
        foreach($configOptionsPayload as $configId => $qty)
        {
            $from = $current[$configId] ?? 0;
            if($qty > $from)
            {
                $bought[$configId] = $qty - $from;
            }
            elseif($qty < $from)
            {
                $reduce[$configId] = $qty;
            }
        }

        if($reduce)
        {
            $result = localAPI('UpgradeProduct', [
                'serviceid'     => $serviceId,
                'paymentmethod' => $service->paymentmethod,
                'type'          => 'configoptions',
                'configoptions' => $reduce,
            ]);
            if(!isset($result['result']) || $result['result'] !== 'success')
            {
                throw new \Exception(isset($result['message']) ? $result['message'] : 'UpgradeProduct API failed');
            }
        }

        $message = Lang::absoluteT('acme_upgrade_success');
        if($bought)
        {
            $invoiceId = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::createSlotPurchaseInvoice($serviceId, $bought);
            if($invoiceId > 0)
            {
                global $CONFIG;
                $message = Lang::absoluteT('acme_slots_invoice_created') . ' <a target="_blank" href="' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . (int)$invoiceId . '">'
                    . Lang::absoluteT('Click here to view the invoice') . '</a>';
            }
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    public function removeDomainJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['id'];
        $domain    = isset($input['domain']) ? strtolower(trim($input['domain'])) : '';
        if(empty($domain))
        {
            throw new \Exception(Lang::absoluteT('domain_is_required'));
        }

        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        $api->removeAcmeDomain((int)$subscription->acme_id, [
            'domain' => $domain,
        ]);

        AcmeSubscriptionDomain::markDomainAsRemoved($serviceId, $domain);

        $acmeSubscriptionDomain = AcmeSubscriptionDomain::where('service_id', $serviceId)
                                                        ->where('domain', $domain)
                                                        ->first();

        $addedAt      = $acmeSubscriptionDomain ? strtotime((string)$acmeSubscriptionDomain->added_at) : false;
        $withinWindow = $addedAt !== false && (time() - $addedAt) < (30 * 24 * 60 * 60);

        if($withinWindow)
        {
            // Added within 30 days — delete the row entirely so the slot is freed. No money is credited here; the text
            // (despite the key name) only says that the slot limit was reduced.
            $acmeSubscriptionDomain->delete();

            return [
                'success' => true,
                'message' => Lang::absoluteT('acme_domain_removed_and_refunded'),
            ];
        }

        return [
            'success' => true,
            'message' => Lang::absoluteT('acme_domain_removed_successfully'),
        ];
    }

    public function cancelSubscriptionJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->acme_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }
        if(in_array(strtolower((string)$subscription->status), ['cancelled', 'terminated'], true))
        {
            throw new \Exception(Lang::absoluteT('subscriptionAlreadyCancelled'));
        }

        // acme_id is the CaaS item ID (items[0].id), not the order ID; within the refund window the vendor refunds the order
        $refundable = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::orderRefundable($serviceId);
        CertificateService::api($serviceId)->cancelItem((int)$subscription->acme_id, 'Cancelled by client', 'caas', $refundable);

        $subscription->status       = 'cancelled';
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->auto_renew   = 0;
        $subscription->save();

        Capsule::table('tblhosting')->where('id', $serviceId)->update([
                                                                          'domainstatus' => 'Cancelled'
                                                                      ]);

        $refundedCreditsAmount = \ModulesGarden\TSSGGSModule\App\Libs\SubscriptionBilling::refundOnCancel($serviceId, 'Subscription cancelled by the client');
        $message               = Lang::absoluteT('acme_subscription_cancelled');
        if($refundedCreditsAmount > 0)
        {
            $message = sprintf(
                Lang::absoluteT('acme_subscription_cancelled_and_credits_refunded'),
                number_format((float)$refundedCreditsAmount, 2, '.', '')
            );
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    public function stopAutoRenewalJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId    = (int)$input['id'];
        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        $api = Helpers::getApiByServiceId($serviceId);
        $api->disableSubscriptionAutoRenewal((int)$subscription->acme_id);
        $subscription->auto_renew = 0;
        $subscription->save();

        return [
            'success' => true,
            'message' => Lang::absoluteT('acme_auto_renewal_disabled'),
        ];
    }

    /**
     * Contacts sent with a subscription order: the client (the vendor sends its notices there)
     */
    private function subscriptionContacts(array $params)
    {
        $c       = isset($params['clientsdetails']) && is_array($params['clientsdetails']) ? $params['clientsdetails'] : [];
        $contact = [
            'first_name' => (string)($c['firstname'] ?? ''),
            'last_name'  => (string)($c['lastname'] ?? ''),
            'email'      => (string)($c['email'] ?? ''),
            'phone'      => (string)($c['phonenumberformatted'] ?? ($c['phonenumber'] ?? '')),
        ];

        return ['primary' => $contact, 'technical' => $contact];
    }

    public function checkCertificateDetailsJSON($input, $vars = array())
    {
        $serviceId = (int)$input['id'];

        AcmeHelpers::refreshAcmeCertificateDetails($serviceId);

        return [
            'success' => true,
            'message' => Lang::absoluteT('certificate_details_refreshed'),
        ];
    }
}
