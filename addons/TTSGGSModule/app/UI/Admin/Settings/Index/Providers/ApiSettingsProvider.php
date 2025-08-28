<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Routing\Url;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class ApiSettingsProvider extends CrudProvider
{
    const ACTION_TEST_CONNECTION = 'testConnection';

    public function read()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        $this->data['tssLivePartnerCode'] = $moduleConfiguration['credentials']['tss']['PartnerCode'];
        $this->data['tssLiveAuthToken']   = $moduleConfiguration['credentials']['tss']['AuthToken'];
        $this->data['tssTestPartnerCode'] = $moduleConfiguration['credentials']['tss']['TestPartnerCode'];
        $this->data['tssTestAuthToken']   = $moduleConfiguration['credentials']['tss']['TestAuthToken'];
        $this->data['OperationMode']      = $moduleConfiguration['credentials']['tss']['OperationMode'];

        $this->data['ggsLivePartnerCode'] = $moduleConfiguration['credentials']['ggs']['PartnerCode'];
        $this->data['ggsLiveAuthToken']   = $moduleConfiguration['credentials']['ggs']['AuthToken'];
    }

    public function update()
    {
        $moduleRepository        = new AddonModuleRepository();
        $sslStoreLivePartnerCode = $this->formData['tssLivePartnerCode'];
        $sslStoreLiveAuthToken   = $this->formData['tssLiveAuthToken'];
        $sslStoreTestPartnerCode = $this->formData['tssTestPartnerCode'];
        $sslStoreTestAuthToken   = $this->formData['tssTestAuthToken'];
        $goGetSslLivePartnerCode = $this->formData['ggsLivePartnerCode'];
        $goGetSslLiveAuthToken   = $this->formData['ggsLiveAuthToken'];
        $operationMode           = $this->formData['OperationMode'];

        $test = $this->testConnection();
        if($test !== true) return $test;

        $moduleRepository->saveCredentials($sslStoreLivePartnerCode, $sslStoreLiveAuthToken, $goGetSslLivePartnerCode, $goGetSslLiveAuthToken, $sslStoreTestPartnerCode, $sslStoreTestAuthToken, $operationMode);

        $page = Request::get('mg-page');
        if($page === 'configuration')
        {
            return (new Response())->setSuccess($this->translate('successAPI'))->setActions([new Redirect(Url::route('',
                ['module' => 'TTSGGSModule', 'mg-page' => 'configuration', 'mg-action' => 'step5']
            ))]);
        }

    }

    public function testConnection()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        $sslStoreLivePartnerCode = $this->formData['tssLivePartnerCode'];
        $sslStoreLiveAuthToken   = $this->formData['tssLiveAuthToken'];
        $sslStoreTestPartnerCode = $this->formData['tssTestPartnerCode'];
        $sslStoreTestAuthToken   = $this->formData['tssTestAuthToken'];
        $operationMode           = $this->formData['OperationMode'];
        $goGetSslLivePartnerCode = $this->formData['ggsLivePartnerCode'];
        $goGetSslLiveAuthToken   = $this->formData['ggsLiveAuthToken'];

        $apis = [];

        if(in_array('ggs', $moduleConfiguration['vendors']))
        {
            $apis['GGS'] = new SSLTrustCenterApi('GGS', $goGetSslLivePartnerCode, $goGetSslLiveAuthToken);
        }

        if(in_array('tss', $moduleConfiguration['vendors']))
        {
            if($operationMode == 'sandbox')
            {
                $apis['TSS'] = new SSLTrustCenterApi('TSS', $sslStoreTestPartnerCode, $sslStoreTestAuthToken);
            }
            else
            {
                $apis['TSS'] = new SSLTrustCenterApi('TSS', $sslStoreLivePartnerCode, $sslStoreLiveAuthToken);
            }
        }

        $errors = [];

        foreach($apis as $apiType => $api)
        {
            try
            {
                $api->getProducts();
                return true;
            }
            catch(\Throwable $e)
            {
                $apiDisplay = ($apiType == 'GGS') ? 'GoGetSSL API' : 'The SSL STORE API';

                $errors[] = $apiDisplay . ' - ' . $this->translate('connectionError');
            }
        }

        if($errors)
        {
            return (new Response())
                ->setError(implode("<br>", $errors));
        }
    }
}