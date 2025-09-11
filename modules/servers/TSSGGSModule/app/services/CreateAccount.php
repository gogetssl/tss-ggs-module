<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TSSGGSModule\Configuration;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;

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

        if(isset($_SESSION['TSSGGSModule']['importedServices']) && is_array($_SESSION['TSSGGSModule']['importedServices']) && in_array($serviceId, $_SESSION['TSSGGSModule']['importedServices']))
        {
            $key = array_search($serviceId, $_SESSION['TSSGGSModule']['importedServices']);
            unset($_SESSION['TSSGGSModule']['importedServices'][$key]);
            return 'success';
        }

        EmailTemplates::sendEmail(EmailTemplates::CONFIGURATION_TEMPLATE, $serviceId, [
            'ssl_configuration_link' => (new Config())->getConfigureSSLUrl($sslId, $serviceId),
        ]);

        return 'success';
    }
}