<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Gateway\Endpoints;

use ModulesGarden\TSSGGSModule\App\Libs\Certificates\FreeName;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\LeiService;
use ModulesGarden\TSSGGSModule\App\Libs\ProductLines\PciService;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * TLS (SSL) certificate orders: order, renew, reissue, validation, SANs, status updates, files, cancellation.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait TlsOrders
{
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
        return $this->call('/certificates/' . $this->category . '/' . (int)$itemId, $this->withoutFreeName($data));
    }

    /**
     * A TLS request without the free www / base-domain name in its SAN lists when the vendor adds it itself
     * (TheSSLStore, see FreeName)
     */
    protected function withoutFreeName($data)
    {
        return is_array($data) && $this->category === 'tls' ? FreeName::stripRequest($this->type, $data) : $data;
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
        $data              = $this->withoutFreeName($data);
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

    /**
     * Buys a TLS certificate without its details (POST /certificates/tls/incomplete): product {id, term} and the
     * number of alternative names {single, wildcard}. The answer's items[].token completes it (completeOrder()).
     */
    public function createIncomplete(array $data, $serviceId)
    {
        $this->serviceHint = $serviceId;

        return $this->call('/certificates/' . $this->category . '/incomplete', $data);
    }

    /**
     * Sends the details of a certificate bought with createIncomplete() (PUT /certificates/tls/{token}): the CSR, the
     * common name, the alternative names, the contacts and an optional renewal - the fields of addOrder() without
     * product. Answers with the order details (as getOrder()). POST on the same URL is the reissue call.
     * The details are kept on the purchase row (Request::currentOrder(), addOrder or renewOrder) next to its product,
     * as addOrder() / renewOrder() keep theirs: an automatic renewal places the next order with them.
     */
    public function completeOrder($token, array $data, $serviceId)
    {
        $this->serviceHint = $serviceId;
        unset($data['product']);
        $data  = $this->withoutFreeName($data);
        $order = $this->call('/certificates/' . $this->category . '/' . rawurlencode((string)$token), $data, 'PUT');

        $row = Request::currentOrder($serviceId);
        if($row)
        {
            $stored = json_decode((string)\decrypt((string)$row->request), true);
            $stored = is_array($stored) ? $stored : [];
            Request::where('id', $row->id)->update([
                'request'    => \encrypt(json_encode(array_intersect_key($stored, ['product' => true]) + $data)),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $order;
    }

    public function renewOrder($serviceId, $data)
    {
        $this->serviceHint = $serviceId;
        $data              = $this->withoutFreeName($data);
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

    public function getCertificateDetails($category, $orderId)
    {
        // the web security products and LEI codes are not certificates: they have their own path
        if((string)$category === self::CODE_GUARD_CATEGORY)
        {
            return $this->getCodeGuardDetails($orderId);
        }
        if((string)$category === PciService::CATEGORY)
        {
            return $this->call('/pci/' . (int)$orderId, [], 'GET');
        }
        if((string)$category === LeiService::CATEGORY)
        {
            return $this->call('/lei/' . (int)$orderId, [], 'GET');
        }

        return $this->call('/certificates/' . rawurlencode((string)$category) . '/' . (int)$orderId, [], 'GET');
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
}
