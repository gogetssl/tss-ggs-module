<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;

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

            return $this->getAutoInstallDetails($params['serviceid']);

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

    private function getAutoInstallDetails($serviceId)
    {
        try {
            $autoInstall = AutoInstallHelpers::getStatusForView($serviceId, 20);
        } catch (\Exception $ex) {
            return [];
        }

        if (!$autoInstall) {
            return ['SSL Install Type' => 'Manual'];
        }

        $labels = [
            AutoInstallHelpers::STATUS_AWAITING  => 'label-info',
            AutoInstallHelpers::STATUS_INSTALLED => 'label-success',
            AutoInstallHelpers::STATUS_FAILED    => 'label-danger',
            AutoInstallHelpers::STATUS_DISABLED  => 'label-warning',
        ];
        $label = isset($labels[$autoInstall['status']]) ? $labels[$autoInstall['status']] : 'label-default';

        $status = '<span class="label ' . $label . '">' . htmlspecialchars(ucwords(str_replace('_', ' ', $autoInstall['status']))) . '</span>'
            . ' on <strong>' . htmlspecialchars($autoInstall['domain']) . '</strong>';
        if ($autoInstall['installed_at']) {
            $status .= ' | installed: ' . htmlspecialchars($autoInstall['installed_at']);
        }
        if ($autoInstall['valid_till']) {
            $status .= ' | valid till: ' . htmlspecialchars($autoInstall['valid_till']);
        }
        if ($autoInstall['last_error']) {
            $status .= '<br><span style="color:#c0392b;">Last error: ' . htmlspecialchars($autoInstall['last_error']) . '</span>';
        }

        $return = ['SSL Install Type' => 'AutoInstall (cPanel)', 'AutoInstall Status' => $status];

        if (!empty($autoInstall['logs'])) {
            $logs = '<div style="max-height:220px;overflow:auto;font-size:11px;">';
            foreach ($autoInstall['logs'] as $log) {
                $color = $log['type'] === 'error' ? '#c0392b' : ($log['type'] === 'success' ? '#27ae60' : '#333');
                $logs .= '<div style="color:' . $color . ';">' . htmlspecialchars($log['date']) . ' - ' . htmlspecialchars($log['message']) . '</div>';
            }
            $logs .= '</div>';
            $return['AutoInstall Log'] = $logs;
        }

        return $return;
    }
}