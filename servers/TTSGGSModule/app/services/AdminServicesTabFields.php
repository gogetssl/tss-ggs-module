<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\SSL;

class AdminServicesTabFields
{

    public function run($params)
    {
        $return = [];
        return array_merge($return, $this->getCertificateDetails($params));
    }

    private function getCertificateDetails($params)
    {

        try {
            $sslService = SSL::getByServiceId($params['serviceid']);
            if (is_null($sslService)) {
                throw new \Exception('The create action has not been initialized');
            }

            if ($sslService->status === 'Awaiting Configuration') {
                return ['Configuration Status' => '<span class="label label-warning">Awaiting Configuration</span>'];
            }

            if (empty($sslService->remoteid)) {
                throw new Exception('The id not exist in this SSL order');
            }

            return [];

//            $return = [];
//            $return['SSLCenter API Order ID'] = $sslService->remoteid;
//
//            $orderDetails = (array)$sslService->configdata;
//
//            if(!$orderDetails['domain'])
//            {
//                $configDataUpdate = new \MGModule\SSLCENTERWHMCS\eServices\provisioning\UpdateConfigData($sslService);
//                $orderStatus = $configDataUpdate->run();
//
//                $sslService = $ssl->getByServiceId($this->p['serviceid']);
//                $orderDetails = (array)$sslService->configdata;
//            }
//
//            $return['Cron Synchronized'] = isset($orderDetails['synchronized']) && !empty($orderDetails['synchronized']) ? $orderDetails['synchronized'] : 'Not synchronized';
//            $return['Comodo Order ID'] = $orderDetails['partner_order_id']?:"-";
//            $return['Configuration Status'] = $sslService->status;
//            $return['Domain'] = $orderDetails['domain'];
//            $return['Order Status'] = ucfirst($orderDetails['ssl_status']);
//            if(isset($orderDetails['approver_method']->email) && !empty($orderDetails['approver_method']->email))
//            {
//                $return['Approver email'] = $orderDetails['approver_method']->email;
//            }
//            $return['Order Status Description'] = $orderDetails['order_status_description'] ? : '-';
//
//            if($orderDetails['ssl_status'] == 'active') {
//                $return['Valid From'] = $orderDetails['valid_from'];
//                $return['Expires'] = $orderDetails['valid_till'];
//            }
//
//            foreach ($orderDetails['san_details'] as $key => $san) {
//                $return['SAN ' . ($key + 1)] = sprintf('%s / %s', $san->san_name, $san->status_description);
//            }
//
//            return $return;

        } catch (\Exception $ex) {
            return ['Error' => $ex->getMessage()];
        }
    }
}