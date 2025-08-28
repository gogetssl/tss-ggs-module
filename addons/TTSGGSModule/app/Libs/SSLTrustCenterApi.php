<?php

namespace ModulesGarden\TTSGGSModule\App\Libs;

use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use stdClass;

class SSLTrustCenterApi {

    //protected $apiUrl = 'https://my.gogetssl.com/api/whmcs/v1';
    protected $apiUrl = 'https://my.gogetssl.com/api/v2';
    protected $key;
    protected $code;
    protected $type;
    protected $category;

    public function __construct($type, $code, $key, $category = 'tls') {
        $this->type = $type;
        $this->code = $code;
        $this->key = $key;
        $this->category = $category;
    }

    public function getProducts() {
        return $this->call('/products/');
    }

    public function getProduct($id) {
        return $this->call('/products/'.$id);
    }


    public function getDomainEmails($domains) {
        $postData ['domains'] = $domains;
        return $this->call('/certificates/'.$this->category.'/validation/emails/', $postData);
    }

    public function changeApprovers($order_id, $data) {
        return $this->call('/certificates/'.$this->category.'/'.$order_id.'/validation/change/', $data, 'PATCH');
    }

    public function reissueCertificate($order_id, $data)
    {
        return $this->call('/certificates/'.$this->category.'/'.$order_id, $data);
    }

    public function cancelOrder($order_id, $reason)
    {
        $data = [
            'reason' => $reason
        ];
        return $this->call('/certificates/'.$order_id.'/cancel', $data);
    }

    public function addOrder($data, $serviceId) {
        $order = $this->call('/certificates/'.$this->category.'/', $data);
        Request::insert([
            'serviceid' => $serviceId,
            'name' => 'addOrder',
            'request' => \encrypt(json_encode($data)),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $order;
    }

    public function renewOrder($serviceId, $data)
    {
        $order = $this->call('/certificates/'.$this->category.'/', $data);
        Request::insert([
            'serviceid' => $serviceId,
            'name' => 'renewOrder',
            'request' => \encrypt(json_encode($data)),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $order;
    }

    public function ordersUpdate($timestamp = null)
    {
        return $this->call('/orders/updates?since='.$timestamp);
    }

    public function getOrder($order_id, $serviceId = false) {

        $request = new \stdClass();

        if($serviceId !== false) {
            $request = Request::where('serviceid', $serviceId)->where('name', 'addOrder')->first();
            $requestRenew = Request::where('serviceid', $serviceId)->where('name', 'renewOrder')->first();

            if(isset($requestRenew->request) && !empty($requestRenew->request))
            {
                $request = $requestRenew;
            }
        }

        $response = $this->call('/certificates/'.$this->category.'/'.$order_id);
        if(isset($request->request) && !empty($request->request))
        {
            $response['orderData'] = json_decode(\decrypt($request->request),true);
        }
        return $response;
    }

    public function exportOrder($orderIds)
    {
        $endpoint = '/orders/export';

        foreach($orderIds as $index => $orderId)
        {
            if($index == 0)
            {
                $endpoint .= '?';
            }
            else
            {
                $endpoint .= '&';
            }

            $endpoint .= 'order[]=' . $orderId;
        }

        $response = $this->call($endpoint, [], false, true);
        $return   = [];

        foreach($response as $orderData)
        {
            $orderId = $orderData['order']['id'];

            if(!$orderId)
            {
                throw new \Exception('Invalid response from API');
            }

            $return[$orderId] = $orderData;
        }

        //Helpers::debugLog('exportOrder', $orderIds, $return);

        return $return;
    }

    public function getCertificateFiles($id)
    {
        return $this->call('/certificates/'.$this->category.'/'.$id.'/files');
    }

    public function getManager($countryCode) {
        return $this->call('/profile/manager/?country='.$countryCode);
    }

    public function getAnnouncements() {
        return $this->call('/dashboard/announcements/');
    }

    protected function call($uri, $postData = [], $customMethod = false, $multilineResponse = false) {

        global $CONFIG;

        $versionModule = Config::get('version', '1.0.0');
        $moduleName = str_replace(' ', '', Config::get('systemName', 'TTSGGSModule'));

        $url = $this->apiUrl . $uri;

        $post = !empty($postData) ? true : false;
        $c = curl_init($url);
        if ($post && $customMethod === false) {
            curl_setopt($c, CURLOPT_POST, true);
        }

        if($customMethod !== false) {
            curl_setopt($c, CURLOPT_CUSTOMREQUEST, $customMethod);
        }

        $queryData = '';
        if (!empty($postData)) {
            $queryData = json_encode($postData);
            curl_setopt($c, CURLOPT_POSTFIELDS, $queryData);
        }

        $headers = [];
        $headers[] = 'Authorization: '.$this->type.' '.$this->code.':'.$this->key;
        $headers[] = 'User-Agent: whmcs/'.$CONFIG['Version'].'|'.$moduleName.'/'.$versionModule;

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLINFO_HEADER_OUT,true);
        curl_setopt($c, CURLOPT_HEADER,true);

        $data = curl_exec($c);
        $info = curl_getinfo($c);
        $result = substr($data, $info['header_size']);

        logModuleCall('tss_gss_module','Time: '.$info['total_time'].' '.$uri, $info['request_header'].$queryData, $data);

        if ($result === false) {
            throw new \Exception(curl_error($c));
        }

        curl_close($c);

        if($multilineResponse)
        {
            $result = '[' . str_replace("\n", ',', trim($result)) . ']';
        }

        $response = json_decode($result,true);
        if($response === null)
        {
            throw new \Exception('Invalid response from API');
        }

        if($info['http_code'] != '200' && $info['http_code'] != '201')
        {
            if(isset($response['message']))
            {
                throw new \Exception('Invalid response code: '.$info['http_code'].' '.$response['message']);
            }
            throw new \Exception('Invalid response code: '.$info['http_code']);
        }

        if(isset($response['message']) && !empty($response['message']))
        {
            throw new \Exception($response['message']);
        }

        return $response;
    }
}


