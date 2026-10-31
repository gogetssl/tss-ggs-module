<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use stdClass;

class SSLTrustCenterApi
{

    protected $apiUrl = 'https://my.gogetssl.com/whmcs/v2';
    protected $key;
    protected $code;
    protected $type;
    protected $category;

    public function __construct($type, $code, $key, $category = 'tls')
    {
        $this->type     = $type;
        $this->code     = $code;
        $this->key      = $key;
        $this->category = $category;
    }

    public function getProducts()
    {
        return $this->call('/products');
    }

    public function getProduct($id)
    {
        return $this->call('/products/' . $id);
    }


    public function getDomainEmails($domains)
    {
        $postData ['domains'] = $domains;
        return $this->call('/certificates/' . $this->category . '/validation/emails', $postData);
    }

    /**
     * Change DCV method of a single domain - $data: ['domain' => ['name' => .., 'validation_method' => ..]]
     */
    public function changeApprovers($itemId, $data)
    {
        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId . '/validation/change', $data, 'PATCH');
    }

    /**
     * Asks the CA to check the domain validation (DCV) again - $itemId is the certificate (item) ID.
     * The API expects a JSON array of domain names; when $domains is empty all not yet approved domains are rechecked.
     */
    public function recheckValidation($itemId, array $domains = [])
    {
        if(empty($domains))
        {
            $details = $this->getCertificateDetails($this->category, $itemId);
            foreach(isset($details['items'][0]['domains']) ? (array)$details['items'][0]['domains'] : [] as $domain)
            {
                if(!empty($domain['name']) && empty($domain['approved']))
                {
                    $domains[] = $domain['name'];
                }
            }
        }

        if(empty($domains))
        {
            return [];
        }

        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId . '/validation/recheck', array_values($domains), 'POST');
    }

    /**
     * Resends DCV emails (only for the email validation method)
     */
    public function resendValidationEmail($itemId)
    {
        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId . '/validation/resend', [], 'POST');
    }

    /**
     * Buys additional SANs - $data: ['single_san' => int, 'wildcard_san' => int]
     */
    public function addSan($itemId, $data)
    {
        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId . '/add-san', $data, 'POST');
    }

    public function reissueCertificate($itemId, $data)
    {
        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId, $data);
    }

    /**
     * Cancels the whole order (all items of the order are cancelled too) - $orderId is the order ID (order.id).
     * To cancel a single certificate / service by its item ID use cancelItem().
     */
    public function cancelOrder($orderId, $reason)
    {
        $data = [
            'reason' => !empty($reason) ? $reason : 'Cancelled by API client'
        ];
        return $this->call('/orders/' . (int)$orderId . '/cancel', $data, 'POST');
    }

    public function addOrder($data, $serviceId)
    {
        $order = $this->call('/certificates/' . $this->category, $data);
        Request::insert([
                            'serviceid'  => $serviceId,
                            'name'       => 'addOrder',
                            'request'    => \encrypt(json_encode($data)),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
        return $order;
    }

    public function renewOrder($serviceId, $data)
    {
        $order = $this->call('/certificates/' . $this->category, $data);
        Request::insert([
                            'serviceid'  => $serviceId,
                            'name'       => 'renewOrder',
                            'request'    => \encrypt(json_encode($data)),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
        return $order;
    }

    /**
     * Certificate (item) ID from an order response: {"order":{"id":..},"items":[{"id":..}]}.
     * tblsslorders.remoteid, /certificates/* endpoints and /services/updates use this ID, not order.id.
     * $default is returned when the response has no items (e.g. reissue of the same certificate).
     */
    public static function getItemId($response, $default = null)
    {
        if(isset($response['items'][0]['id']) && (int)$response['items'][0]['id'] > 0)
        {
            return (int)$response['items'][0]['id'];
        }

        if($default !== null)
        {
            return (int)$default;
        }

        throw new \Exception('Invalid response from API: missing certificate item ID');
    }

    public function ordersUpdate($timestamp = null)
    {
        return $this->call('/services/updates?since=' . $timestamp);
    }

    public function getOrder($order_id, $serviceId = false)
    {

        $request = new \stdClass();

        if($serviceId !== false)
        {
            $request      = Request::where('serviceid', $serviceId)->where('name', 'addOrder')->first();
            $requestRenew = Request::where('serviceid', $serviceId)->where('name', 'renewOrder')->first();

            if(isset($requestRenew->request) && !empty($requestRenew->request))
            {
                $request = $requestRenew;
            }
        }

        $response = $this->call('/certificates/' . $this->category . '/' . $order_id);
        if(isset($request->request) && !empty($request->request))
        {
            $response['orderData'] = json_decode(\decrypt($request->request), true);
        }
        return $response;
    }

    /**
     * The /orders/export endpoint is disabled in API v2 - the details are fetched one by one
     * (Get certificate details). Returned array is keyed by the given ID.
     */
    public function exportOrder($orderIds)
    {
        $return = [];

        foreach($orderIds as $orderId)
        {
            $orderData = $this->call('/certificates/' . rawurlencode((string)$this->category) . '/' . (int)$orderId);

            if(empty($orderData['order']['id']))
            {
                throw new \Exception('Invalid response from API');
            }

            $return[$orderId] = $orderData;
        }

        return $return;
    }

    public function getCertificateFiles($id)
    {
        return $this->call('/certificates/' . $this->category . '/' . $id . '/files');
    }

    public function getManager($countryCode, $forceAPI = false)
    {
        if($forceAPI === false)
        {
            $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

            if(isset($request->request) && !empty($request->request))
            {
                return json_decode(\decrypt($request->request), true);
            }
            else
            {
                $response = $this->call('/profile/manager?country=' . $countryCode);

                $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

                if(!isset($request->id))
                {
                    Request::insert([
                                        'serviceid'  => '0',
                                        'name'       => 'manager',
                                        'request'    => \encrypt(json_encode($response)),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                }
                else
                {
                    Request::where('id', $request->id)->update([
                                                                   'request'    => \encrypt(json_encode($response)),
                                                                   'updated_at' => date('Y-m-d H:i:s')
                                                               ]);
                }

                return $response;
            }

        }
        else
        {
            $response = $this->call('/profile/manager?country=' . $countryCode);

            $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

            if(!isset($request->id))
            {
                Request::insert([
                                    'serviceid'  => '0',
                                    'name'       => 'manager',
                                    'request'    => \encrypt(json_encode($response)),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            }
            else
            {
                Request::where('id', $request->id)->update([
                                                               'request'    => \encrypt(json_encode($response)),
                                                               'updated_at' => date('Y-m-d H:i:s')
                                                           ]);
            }

            return $response;
        }
    }

    public function getAnnouncements($forceAPI = false)
    {
        if($forceAPI === false)
        {
            $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

            if(isset($request->request) && !empty($request->request))
            {
                return json_decode(\decrypt($request->request), true);
            }
            else
            {
                $response = $this->call('/dashboard/announcements');

                $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

                if(!isset($request->id))
                {
                    Request::insert([
                                        'serviceid'  => '0',
                                        'name'       => 'announcements',
                                        'request'    => \encrypt(json_encode($response)),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                }
                else
                {
                    Request::where('id', $request->id)->update([
                                                                   'request'    => \encrypt(json_encode($response)),
                                                                   'updated_at' => date('Y-m-d H:i:s')
                                                               ]);
                }

                return $response;
            }

        }
        else
        {
            $response = $this->call('/dashboard/announcements');

            $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

            if(!isset($request->id))
            {
                Request::insert([
                                    'serviceid'  => '0',
                                    'name'       => 'announcements',
                                    'request'    => \encrypt(json_encode($response)),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            }
            else
            {
                Request::where('id', $request->id)->update([
                                                               'request'    => \encrypt(json_encode($response)),
                                                               'updated_at' => date('Y-m-d H:i:s')
                                                           ]);
            }

            return $response;
        }
    }

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
        $items  = isset($status['items']) && is_array($status['items']) ? $status['items'] : array();

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
            $data['product'] = ['id' => (int)$data['product_id']];
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

    public function getCertificateDetails($category, $orderId)
    {
        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$orderId, [], 'GET');
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
     * Cancels a certificate / service by its item ID and category (tls, cs, smime, ais).
     * TSS has no item cancellation - the order ID equals the item ID there, so /orders/{id}/cancel is used.
     */
    public function cancelItem($itemId, $reason = '', $category = null)
    {
        $reason = !empty($reason) ? $reason : 'Cancelled by API client';

        if(strtoupper($this->type) === 'TSS')
        {
            return $this->cancelOrder($itemId, $reason);
        }

        $category = $category ?: $this->category;

        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$itemId . '/cancel', ['reason' => $reason], 'POST');
    }

    /**
     * Turns subscription auto renewal on / off (AIS)
     */
    public function changeSubscriptionAutoRenewal($category, $itemId, $autoRenew)
    {
        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$itemId . '/subscription', ['auto_renew' => (bool)$autoRenew], 'PATCH');
    }

    public function disableSubscriptionAutoRenewal($itemId)
    {
        return $this->call('/certificates/caas/' . (int)$itemId . '/subscription', [], 'DELETE');
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

    protected function call($uri, $postData = [], $customMethod = false, $multilineResponse = false)
    {

        global $CONFIG;

        $versionModule = Config::get('version', '1.0.0');
        $moduleName    = str_replace(' ', '', Config::get('systemName', 'TSSGGSModule'));

        $url = $this->apiUrl . $uri;

        $post = !empty($postData) ? true : false;
        $c    = curl_init($url);
        if($post && $customMethod === false)
        {
            curl_setopt($c, CURLOPT_POST, true);
        }

        if($customMethod !== false)
        {
            curl_setopt($c, CURLOPT_CUSTOMREQUEST, $customMethod);
        }

        $queryData = '';
        if(!empty($postData))
        {
            $queryData = json_encode($postData);
            curl_setopt($c, CURLOPT_POSTFIELDS, $queryData);
        }

        $headers   = [];
        $headers[] = 'Authorization: ' . $this->type . ' ' . $this->code . ':' . $this->key;
        $headers[] = 'User-Agent: whmcs/' . $CONFIG['Version'] . '|' . $moduleName . '/' . $versionModule;

        if($queryData !== '')
        {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($queryData);
        }
        elseif(in_array($customMethod, ['POST', 'PUT', 'PATCH'], true))
        {
            // Request without body (e.g. validation recheck) - some servers reject it without the length header
            $headers[] = 'Content-Length: 0';
        }

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLINFO_HEADER_OUT, true);
        curl_setopt($c, CURLOPT_HEADER, true);

        $data   = curl_exec($c);
        $info   = curl_getinfo($c);
        $result = substr($data, $info['header_size']);

        logModuleCall('tss_gss_module', 'Time: ' . $info['total_time'] . ' ' . $uri, $info['request_header'] . $queryData, $data);

        if($result === false)
        {
            throw new \Exception(curl_error($c));
        }

        curl_close($c);

        $httpCode  = (int)$info['http_code'];
        $isSuccess = $httpCode >= 200 && $httpCode < 300;

        if($httpCode === 204) // No Content
        {
            return [];
        }

        if(trim((string)$result) === '')
        {
            if($isSuccess)
            {
                return [];
            }
            throw new \Exception('Invalid response code: ' . $httpCode);
        }

        if($multilineResponse)
        {
            $result = '[' . str_replace("\n", ',', trim($result)) . ']';
        }

        $response = json_decode($result, true);
        if($response === null)
        {
            throw new \Exception('Invalid response from API');
        }

        if(!$isSuccess)
        {
            if(isset($response['message']))
            {
                throw new \Exception('Invalid response code: ' . $httpCode . ' ' . $response['message']);
            }
            throw new \Exception('Invalid response code: ' . $httpCode);
        }

        return $response;
    }
}


