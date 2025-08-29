<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\Smarty;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

class SSLStepOne
{
    public function run($params)
    {
        $sans = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $sans_wildcard = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $sans += $params['configoption7'];
        $sans_wildcard += $params['configoption8'];

        $clientData = $params['model']->client->toArray();
        $serviceData = $params['model']->toArray();

        $fields['additionalfields'] = [];

        if($sans > 0)
        {
            $fields['additionalfields'][Lang::absoluteT('sans_title')]['sans_domains'] = [
                'FriendlyName' => sprintf(Lang::absoluteT('additional_san'), $sans),
                'Type' => 'textarea',
                'Size' => '30',
                'Description' => Lang::absoluteT('additional_san_desc'),
                'Required' => false

            ];
        }
        if($sans_wildcard > 0)
        {
            $fields['additionalfields'][Lang::absoluteT('sans_title')]['wildcard_san'] = [
                'FriendlyName' => sprintf(Lang::absoluteT('additional_san_wildcard'), $sans_wildcard),
                'Type' => 'textarea',
                'Size' => '30',
                'Description' => Lang::absoluteT('additional_san_wildcard_desc'),
                'Required' => false
            ];
        }
        $fields['additionalfields']['<br />']['js_custom'] = [
            'Type' => 'text',
            'Description' => $this->getJS($params['serviceid'], $clientData, $serviceData['domain'])
        ];
        return $fields;

    }

    private function getJS($serviceId, $clientData, $domain)
    {
        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        if(!isset($configuration['sslSettings']['useProfileDetailsForCsr']) || empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            foreach ($clientData as $key => $value)
            {
                $clientData[$key] = '';
            }
        }

        if(isset($configuration['sslSettings']['useProfileDetailsForCsr']) && !empty($configuration['sslSettings']['useProfileDetailsForCsr']))
        {
            $configuration['sslSettings']['defaultCsrCountry'] = false;
        }

        $countries = file_get_contents(dirname(dirname(dirname(dirname(dirname(__DIR__))))).DS.'resources'.DS.'country'.DS.'dist.countries.json');
        return Smarty::buildTemplate('configuressl/SSLStepOne', [
            'countries' => $countries,
            'serviceId' => $serviceId,
            'enable_csr' => isset($configuration['sslSettings']['enableCsrGenerator']) ? $configuration['sslSettings']['enableCsrGenerator'] : false,
            'use_profile_data' => isset($configuration['sslSettings']['useProfileDetailsForCsr']) ? $configuration['sslSettings']['useProfileDetailsForCsr'] : false,
            'default_csr_country' => isset($configuration['sslSettings']['defaultCsrCountry']) ? $configuration['sslSettings']['defaultCsrCountry'] : false,
            'adminFormHidden' => isset($configuration['sslSettings']['autoDetailsForDvOrders']) ? $configuration['sslSettings']['autoDetailsForDvOrders'] : false,
            'domain' => $domain,
            'client' => $clientData,
            'post' => $_POST
        ]);

    }

}