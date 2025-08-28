<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TTSGGSModule\Configuration;
use ModulesGarden\TTSGGSModule\App\Helpers\EmailTemplates;

class CreateAccount {


    public function run($params)
    {
        $serviceId = $params['serviceid'];
        $userId = $params['userid'];

        $serviceSSL = SSL::getByServiceId($serviceId);
        if (!is_null($serviceSSL)) {
            throw new \Exception('The SSL order has already been created');
        }

        $sslId = SSL::insertGetId([
            'userid' => $userId,
            'serviceid' => $serviceId,
            'remoteid' => '',
            'module' => (new Configuration())->systemName,
            'certtype' => '',
            'completiondate' => '',
            'status' => 'Awaiting Configuration'
        ]);

        if(isset($_SESSION['TTSGGSModule']['importedServices']) && is_array($_SESSION['TTSGGSModule']['importedServices']) && in_array($serviceId, $_SESSION['TTSGGSModule']['importedServices']))
        {
            $key = array_search($serviceId, $_SESSION['TTSGGSModule']['importedServices']);
            unset($_SESSION['TTSGGSModule']['importedServices'][$key]);
            return 'success';
        }

        EmailTemplates::sendEmail(EmailTemplates::CONFIGURATION_TEMPLATE, $serviceId, [
            'ssl_configuration_link' => (new Config())->getConfigureSSLUrl($sslId, $serviceId),
        ]);

        return 'success';
    }
}