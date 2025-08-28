<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\services;

use ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs\Domain;
use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\Smarty;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;

class SSLStepTwo
{

    public function run($params)
    {
        try {

            $decode = $this->decodeCSR($params['csr']);
            $domains = $this->validateDomains($decode['CN'], $params['fields'], $params);

            $configProduct = (new ProductRepository())->getProductConfiguration($params['packageid']);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $available_method_validation = explode(',',$configProduct['dcv']);
            if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
            {
                $key = array_search('email', $available_method_validation);
                if($key !== false) unset($available_method_validation[$key]);

                $temp_available_method_validation = [];
                foreach ($available_method_validation as $method)
                {
                    $temp_available_method_validation[] = $method;
                }
                $available_method_validation = $temp_available_method_validation;
            }
            $vars['available_method_validation'] = json_encode($available_method_validation);

            $provider = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken'] =  $credentials['TestAuthToken'];
            }

            $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
            $approvers = $api->getDomainEmails($domains);
            $vars['approvers'] = $this->approversToOptions($approvers);

            $view = Smarty::buildTemplate('configuressl/SSLStepTwo', $vars);
            return ['approveremails' => [$view]];

        } catch (\Exception $e) {

            return ['error' => $e->getMessage()];

        }
    }

    public function decodeCSR($csr)
    {
        $decode = openssl_csr_get_subject($csr);
        if($decode === false)
        {
            throw new \Exception(Lang::absoluteT('csr_error'));
        }
        return $decode;
    }

    public function validateDomains($commonName, $fields, $params = [])
    {
        $countSan = isset($params['configoptions']['sans']) ? $params['configoptions']['sans'] : 0;
        $countSanWildCard = isset($params['configoptions']['sans_wildcard']) ? $params['configoptions']['sans_wildcard'] : 0;

        $countSan += $params['configoption7'];
        $countSanWildCard += $params['configoption8'];

        $domainValidation = new Domain();

        $domains = '';
        if (!empty($fields['sans_domains'])) {

            $san = explode(',', $fields['sans_domains']);

            if($countSan < count($san))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san'),$countSan));
            }

            foreach ($san as $domain)
            {
                if(empty($domain)) continue;
                $domainValidation->validateDomain($domain);
            }

            $domains .= $fields['sans_domains'];
        }
        if (!empty($fields['wildcard_san'])) {

            $wildcard = explode(',', $fields['wildcard_san']);

            if($countSanWildCard < count($wildcard))
            {
                throw new \Exception(sprintf(Lang::absoluteT('using_max_san_wildcard'),$countSanWildCard));
            }

            foreach ($wildcard as $domain)
            {
                if(empty($domain)) continue;
                $domainValidation->validateDomain($domain, true);
            }

            $domains .= ',' . $fields['wildcard_san'];
        }
        if(empty($domains))
        {
            $domains = $commonName;
        }
        else
        {
            $domains = $commonName . ',' . $domains;
        }
        $domains = explode(',', $domains);

        foreach ($domains as $key => $domain)
        {
            if(empty($domain)) unset($domains[$key]);
            $domains[$key] = trim($domain);
        }

        return $domains;
    }

    public function approversToOptions($approvers)
    {
        $results = [];
        foreach ($approvers as $approver) {
            $options = '';
            foreach ($approver['emails'] as $option) {
                $options .= '<option value="' . $option . '">' . $option . '</option>';
            }
            $results[$approver['domain']] = $options;
        }
        return $results;
    }

}