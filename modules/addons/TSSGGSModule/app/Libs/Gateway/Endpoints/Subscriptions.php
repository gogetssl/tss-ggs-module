<?php

namespace TSSGGSModule\App\Libs\Gateway\Endpoints;

use TSSGGSModule\App\Libs\Subscriptions\AisHelpers;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;

/**
 * Subscriptions: ACME (Automated SSL) and AIS / Plan + Automate.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait Subscriptions
{
    protected function normalizeDomains($domains)
    {
        if(is_array($domains))
        {
            return array_values(array_filter(array_map('trim', $domains)));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string)$domains))));
    }

    protected function findAcmeDomainId($status, $domainName)
    {
        if(!is_array($status))
        {
            return null;
        }

        $target = strtolower(trim((string)$domainName));
        $items  = isset($status['items']) && is_array($status['items']) ? $status['items'] : [];

        foreach($items as $item)
        {
            if(empty($item['domains']) || !is_array($item['domains']))
            {
                continue;
            }

            foreach($item['domains'] as $domain)
            {
                if(!is_array($domain) || !isset($domain['name']))
                {
                    continue;
                }

                if(strtolower(trim((string)$domain['name'])) === $target && !empty($domain['id']))
                {
                    return $domain['id'];
                }
            }
        }

        return null;
    }

    protected function normalizeAcmeCreateResponse($response)
    {
        if(!is_array($response))
        {
            return $response;
        }

        if(isset($response['order']['id']) && !isset($response['order_id']))
        {
            $response['order_id'] = (int)$response['order']['id'];
            $response['id']       = (int)$response['order']['id'];
        }

        if(isset($response['order']['status']) && !isset($response['status']))
        {
            $response['status'] = $response['order']['status'];
        }

        if(!empty($response['items']) && is_array($response['items']) && isset($response['items'][0]) && is_array($response['items'][0]))
        {
            $item = $response['items'][0];

            if(!empty($item['account']) && is_array($item['account']))
            {
                $response['acme_account_id'] = isset($item['account']['id']) ? (string)$item['account']['id'] : '';
                $response['eab_kid']         = isset($item['account']['eab_mac_id']) ? (string)$item['account']['eab_mac_id'] : '';
                $response['eab_hmac_key']    = isset($item['account']['eab_mac_key']) ? (string)$item['account']['eab_mac_key'] : '';
                $response['server_url']      = isset($item['account']['server_url']) ? (string)$item['account']['server_url'] : '';
            }

            if(!empty($item['subscription']) && is_array($item['subscription']))
            {
                $response['begin_date']   = isset($item['subscription']['begin']) ? $item['subscription']['begin'] : null;
                $response['end_date']     = isset($item['subscription']['end']) ? $item['subscription']['end'] : null;
                $response['renewal_date'] = isset($item['subscription']['next_renewal']) ? $item['subscription']['next_renewal'] : null;
            }
        }

        return $response;
    }

    public function createAcmeSubscription($data)
    {
        if(isset($data['product_id']) && !isset($data['product']))
        {
            // TheSSLStore product ids are codes (sectigo_caas_dv), GoGetSSL ids are numbers
            $data['product'] = ['id' => self::productId($data['product_id'])];
            unset($data['product_id']);
        }

        if(isset($data['domains']))
        {
            $data['domains'] = $this->normalizeDomains($data['domains']);
        }

        $createResponse = $this->normalizeAcmeCreateResponse(
            $this->call('/certificates/caas', $data)
        );

        $items     = isset($createResponse['items']) && is_array($createResponse['items']) ? $createResponse['items'] : [];
        $firstItem = isset($items[0]) && is_array($items[0]) ? $items[0] : [];
        $itemId    = isset($firstItem['id']) ? (int)$firstItem['id'] : 0;

        if($itemId <= 0)
        {
            $itemId = isset($createResponse['order_id']) ? (int)$createResponse['order_id'] : 0;
            if($itemId <= 0 && isset($createResponse['id']))
            {
                $itemId = (int)$createResponse['id'];
            }
        }

        if($itemId > 0)
        {
            $detailsResponse = $this->normalizeAcmeCreateResponse(
                $this->getCertificateDetails('caas', $itemId)
            );

            if(is_array($detailsResponse))
            {
                return array_replace($createResponse, $detailsResponse);
            }
        }

        return $createResponse;
    }

    public function getAcmeSubscriptionStatus($itemId)
    {
        return $this->call('/certificates/caas/' . (int)$itemId, [], 'GET');
    }

    public function addAcmeDomains($itemId, $data)
    {
        if(isset($data['domains']))
        {
            $data = $this->normalizeDomains($data['domains']);
        }
        else if(!is_array($data))
        {
            $data = $this->normalizeDomains($data);
        }

        return $this->call('/certificates/caas/' . (int)$itemId . '/domains', $data, 'POST');
    }

    public function removeAcmeDomain($itemId, $data)
    {
        $domainId = null;
        $response = $this->getCertificateDetails('caas', $itemId);

        foreach($response['items'][0]['domains'] as $remoteDomainData)
        {
            if($remoteDomainData['name'] == $data['domain'])
            {
                $domainId = $remoteDomainData['id'];
                break;
            }
        }

        if(empty($domainId))
        {
            throw new \Exception('Unable to resolve ACME domain ID.');
        }

        return $this->call('/certificates/caas/' . (int)$itemId . '/domains/' . rawurlencode((string)$domainId), [], 'DELETE');
    }

    /**
     * Plan + Automate: switches how the certificate is installed - AutoInstall SSL agent or the client's own ACME client
     * ($method 'autoinstall' | 'acme', sent as the gateway's 'ais' | 'acme'). The answer is 204; the details call then
     * reports the new method and, for 'acme', the ACME account (GoGetSSL: after a few minutes).
     */
    public function changeAisInstallMethod($itemId, $method)
    {
        $value = AisHelpers::GATEWAY_METHODS[(string)$method] ?? (string)$method;

        return $this->call('/certificates/ais/' . (int)$itemId . '/installation-method', ['installation_method' => $value], 'PUT');
    }

    /**
     * Turns subscription auto renewal on / off (AIS)
     */
    public function changeSubscriptionAutoRenewal($category, $itemId, $autoRenew)
    {
        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$itemId . '/subscription', ['auto_renew' => (bool)$autoRenew], 'PATCH');
    }

    public function createAis(array $data)
    {
        return $this->call('/certificates/ais', $data);
    }

    public function getAisDetails($itemId)
    {
        return $this->call('/certificates/ais/' . (int)$itemId, [], 'GET');
    }

    public function cancelAisSubscription($itemId, $reason = '')
    {
        return $this->cancelItem($itemId, !empty($reason) ? $reason : 'Cancelled by client', 'ais');
    }

    public function disableAisAutoRenewal($itemId)
    {
        return $this->changeSubscriptionAutoRenewal('ais', $itemId, false);
    }
}
