<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use stdClass;

class SSLTrustCenterApi
{

    const API_URL = 'https://my.gogetssl.com/whmcs/v2';

    // A hung gateway must not stall the crons or the client area pages (seconds)
    const CONNECT_TIMEOUT = 10;
    const TIMEOUT         = 60;

    protected $apiUrl = self::API_URL;
    protected $key;
    protected $code;
    protected $type;
    protected $category;
    protected $serviceHint = null;

    public function __construct($type, $code, $key, $category = 'tls')
    {
        $this->type     = $type;
        $this->code     = $code;
        $this->key      = $key;
        $this->category = $category;
    }

    /**
     * [partner code, auth token] of a vendor as stored in the module settings (TheSSLStore: the sandbox pair while the
     * sandbox mode is on); null when the vendor has no credentials
     */
    public static function credentialsOf($vendor)
    {
        $vendor      = strtolower((string)$vendor);
        $credentials = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['credentials'][$vendor] ?? [];
        $sandbox     = $vendor === 'tss' && ($credentials['OperationMode'] ?? '') === 'sandbox';
        $code        = trim((string)($credentials[$sandbox ? 'TestPartnerCode' : 'PartnerCode'] ?? ''));
        $token       = (string)($credentials[$sandbox ? 'TestAuthToken' : 'AuthToken'] ?? '');

        return $code !== '' ? [$code, $token] : null;
    }

    /**
     * Gateway client of a vendor for a product category
     */
    public static function forVendor($vendor, $category = 'tls')
    {
        $credentials = self::credentialsOf($vendor);
        if($credentials === null)
        {
            throw new \Exception('No gateway credentials configured for ' . Helpers::vendorToDisplay($vendor));
        }

        return new self($vendor, $credentials[0], $credentials[1], $category ?: 'tls');
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
        $this->serviceHint = $serviceId;
        $order             = $this->call('/certificates/' . $this->category, $data);
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
        $this->serviceHint = $serviceId;
        $order             = $this->call('/certificates/' . $this->category, $data);
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

    /**
     * Certificates (item IDs) changed since a unix time - GET /services/updates?since=<unix time>.
     * (/certificates/status is listed in the gateway documentation but does not exist - not used.)
     */
    public function ordersUpdate($timestamp = null)
    {
        $query = is_numeric($timestamp) ? '?since=' . (int)$timestamp : '';

        return (array)$this->call('/services/updates' . $query);
    }

    public function getOrder($order_id, $serviceId = false)
    {

        $request = new \stdClass();

        if($serviceId !== false)
        {
            $this->serviceHint = $serviceId;
            $request = Request::currentOrder($serviceId) ?: new \stdClass();
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
    /**
     * Cancels a certificate or subscription by its item ID. $refund (subscriptions): ask the vendor for the refund of a
     * cancellation within its refund window; null leaves the decision to the gateway.
     */
    public function cancelItem($itemId, $reason = '', $category = null, $refund = null)
    {
        $reason   = !empty($reason) ? $reason : 'Cancelled by API client';
        $category = $category ?: $this->category;

        // TheSSLStore certificates are cancelled as orders; its subscriptions (ACME / Plan + Automate) have their own call
        if(strtoupper($this->type) === 'TSS' && !in_array($category, self::SUBSCRIPTION_CATEGORIES, true))
        {
            return $this->cancelOrder($itemId, $reason);
        }

        $data = ['reason' => $reason];
        if($refund !== null)
        {
            $data['refund'] = (bool)$refund;
        }

        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$itemId . '/cancel', $data, 'POST');
    }

    /** categories of the subscription products */
    const SUBSCRIPTION_CATEGORIES = ['caas', 'ais'];

    /** a product id as the gateway expects it: a number (GoGetSSL) or a product code (TheSSLStore) */
    public static function productId($id)
    {
        $id = trim((string)$id);

        return ctype_digit($id) ? (int)$id : $id;
    }

    /**
     * Plan + Automate: switches how the certificate is installed - AutoInstall SSL agent or the client's own ACME client
     * ($method 'autoinstall' | 'acme'); the answer of the details call then carries the ACME account for 'acme'
     */
    public function changeAisInstallMethod($itemId, $method)
    {
        return $this->call('/certificates/ais/' . (int)$itemId, ['install_method' => (string)$method], 'PATCH');
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

    /**
     * S/MIME order: product {id, term}, emails (first = subject), optional csr and contacts (OV products)
     */
    public function createSmime(array $data)
    {
        return $this->call('/certificates/smime', $data);
    }

    /**
     * Code signing order: product {id, term}, provisioning {method, model | csr | shipping {id, contact}}, contacts
     */
    public function createCodeSigning(array $data)
    {
        return $this->call('/certificates/cs', $data);
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

    protected static function log($action, $request, $response, array $replace = [])
    {
        if(GeneralSettings::enabled('moduleLog'))
        {
            // credentials, EAB keys, SSO links and private keys never go to the log
            logModuleCall('tss_gss_module', $action, SecretValue::redact($request), SecretValue::redact($response), '', $replace);
        }
    }

    /**
     * Every gateway request and its answer goes to the module log (Logs page), so a failed order or sync can be traced
     * to the exact request. Secrets are masked, long bodies are cut, logging never breaks the request.
     */
    protected function logCall($method, $uri, $requestBody, $httpCode, $responseBody, $seconds, $error = '', array $whmcsLog = [])
    {
        $serviceHint       = $this->serviceHint;
        $this->serviceHint = null;

        try
        {
            $path   = strtok($uri, '?');
            $failed = $error !== '' || $httpCode < 200 || $httpCode >= 400;

            // cheap decision first; the service lookup is only needed for services in debug mode
            if(!LogPolicy::debugServices() && !LogPolicy::logApi($failed, $method, null))
            {
                return;
            }
            $reason = $error;
            if($error === '' && $failed)
            {
                $decoded = json_decode((string)$responseBody, true);
                $reason  = is_array($decoded) && isset($decoded['message']) && is_scalar($decoded['message']) ? (string)$decoded['message'] : '';
            }

            $data      = [];
            $serviceId = $serviceHint ?: self::serviceFromUri($uri);
            if(!LogPolicy::logApi($failed, $method, $serviceId))
            {
                return;
            }

            // the same decision governs the WHMCS Module Log (Utilities > Logs > Module Log), if that switch is on
            if($whmcsLog)
            {
                self::log($whmcsLog[0], $whmcsLog[1], $whmcsLog[2], $whmcsLog[3] ?? []);
            }
            if($serviceId)
            {
                $data['service'] = (int)$serviceId;
            }
            $data['api'] = [
                'provider'  => $this->type,
                'method'    => $method,
                'url'       => Edition::get() === Edition::GGS ? $this->apiUrl . $uri : '/whmcs/v2' . $uri,
                'http_code' => $httpCode ?: 'no response',
                'duration'  => round($seconds, 2) . ' s',
                'request'   => self::logBody($requestBody),
                'response'  => self::logBody($error !== '' ? $error : $responseBody),
            ];

            $message = 'API ' . $method . ' ' . $path . ' - ' . ($error !== '' ? 'failed' : 'HTTP ' . $httpCode) . ($reason !== '' ? ': ' . $reason : '');
            $failed ? Logger::error($message, $data) : Logger::info($message, $data);
        }
        catch(\Throwable $e)
        {
            // the log must never break an API request
        }
    }

    /** The service an item / order ID of the request belongs to (tblsslorders keeps the link), null when unknown */
    protected static function serviceFromUri($uri)
    {
        if(!preg_match_all('~/(\d+)(?=/|$|\?)~', (string)$uri, $matches))
        {
            return null;
        }

        return \WHMCS\Database\Capsule::table('tblsslorders')->where('remoteid', (string)end($matches[1]))->value('serviceid') ?: null;
    }

    /** Body for the log: decoded JSON when possible, secrets masked, cut to fit the log column */
    protected static function logBody($body, $limit = 24000)
    {
        if($body === null || $body === '')
        {
            return '';
        }
        $decoded = is_string($body) ? json_decode($body, true) : null;
        $value   = SecretValue::redact(is_array($decoded) ? $decoded : $body);
        $json    = json_encode($value);
        if($json !== false && strlen($json) <= $limit)
        {
            return $value;
        }

        return ['truncated' => true, 'preview' => mb_strcut(is_string($value) ? $value : (string)$json, 0, $limit)];
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
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($c, CURLOPT_TIMEOUT, self::TIMEOUT);

        $started = microtime(true);
        $hint    = $this->serviceHint; // logCall() clears it
        $data    = curl_exec($c);
        $info    = curl_getinfo($c);
        $method  = $customMethod !== false ? strtoupper((string)$customMethod) : ($post ? 'POST' : 'GET');

        if($data === false)
        {
            $error = curl_error($c);
            curl_close($c);
            $this->logCall($method, $uri, $queryData, 0, '', microtime(true) - $started, $error, ['Time: ' . $info['total_time'] . ' ' . $uri, $queryData, $error]);
            throw new \Exception('Gateway request failed: ' . $error);
        }

        $result = substr($data, $info['header_size']);

        $this->logCall($method, $uri, $queryData, (int)$info['http_code'], (string)$result, microtime(true) - $started, '', ['Time: ' . $info['total_time'] . ' ' . $uri, $info['request_header'] . $queryData, $data, array_values(array_filter([(string)$this->key], 'strlen'))]);

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
            throw new \Exception(self::readableError('', $httpCode), $httpCode);
        }

        if($multilineResponse)
        {
            $result = '[' . str_replace("\n", ',', trim($result)) . ']';
        }

        $response = json_decode($result, true);
        if($response === null)
        {
            // an error page of a proxy (502 / 504) is reported with its HTTP code, not as an unreadable answer
            if(!$isSuccess)
            {
                throw new \Exception(self::readableError('', $httpCode), $httpCode);
            }
            throw new \Exception('Invalid response from API');
        }

        if(!$isSuccess)
        {
            // an order refused for lack of funds: tell the administrators at once (Settings > Finance > Reseller balance)
            if(isset($response['message']) && FinancePolicy::isBalanceError($response['message']))
            {
                try
                {
                    FinancePolicy::balanceAlert($response['message'], $hint);
                }
                catch(\Throwable $e)
                {
                }
            }

            throw new \Exception(self::readableError(isset($response['message']) ? $response['message'] : '', $httpCode), $httpCode);
        }

        return $response;
    }

    /**
     * Gateway error as a sentence a client can read (it is shown in the client area as is); the HTTP code stays in
     * the exception code and in the API log. "Vendor returns error:ErrorCode:x|ErrorMessage:y" becomes "y".
     */
    public static function readableError($message, $httpCode)
    {
        $message = trim((string)$message);
        if(preg_match('/ErrorMessage:\s*(.+)$/is', $message, $m))
        {
            $message = trim($m[1]);
        }
        $message = trim(preg_replace('/^Vendor returns error:\s*/i', '', $message));
        $message = rtrim($message, '!.') ;

        if($message === '')
        {
            return $httpCode >= 500
                ? 'The SSL service is temporarily unavailable (HTTP ' . (int)$httpCode . '). Please try again later.'
                : 'The SSL service could not process the request (HTTP ' . (int)$httpCode . ').';
        }

        return $httpCode >= 500
            ? 'The SSL service could not complete the request: ' . $message . '. Please try again later.'
            : $message . '.';
    }
}


