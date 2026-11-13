<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Migration\Credentials;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class ApiSettingsProvider extends CrudProvider
{
    const ACTION_TEST_CONNECTION = 'testConnection';

    /**
     * A stored API token is never sent back to the browser: the field stays empty and the token is kept when it is
     * saved empty (see token()). A "password" input still carries its value in the page, so echoing it would put the
     * live token into the admin's HTML, where a browser extension, a saved page or an XSS in WHMCS could read it.
     *
     * A token prefilled from a legacy module for the migration is not stored yet - it is shown, because the admin has
     * to be able to see and confirm what will be saved.
     */
    public function read()
    {
        // empty vendor blocks are prefilled from the legacy modules chosen for migration (nothing stored until saved)
        [$credentials] = Credentials::prefilled();
        $stored        = (new AddonModuleRepository())->getModuleConfiguration()['credentials'] ?? [];

        $show = function($vendor, $field) use ($credentials, $stored) {
            $value = (string)($credentials[$vendor][$field] ?? '');

            // shown only while it is not the stored one (prefilled from a legacy module, waiting to be saved)
            return $value !== '' && $value !== (string)($stored[$vendor][$field] ?? '') ? $value : '';
        };

        $this->data['tssLivePartnerCode'] = $credentials['tss']['PartnerCode'];
        $this->data['tssLiveAuthToken']   = $show('tss', 'AuthToken');
        $this->data['tssTestPartnerCode'] = $credentials['tss']['TestPartnerCode'];
        $this->data['tssTestAuthToken']   = $show('tss', 'TestAuthToken');
        $this->data['OperationMode']      = $credentials['tss']['OperationMode'];

        $this->data['ggsLivePartnerCode'] = $credentials['ggs']['PartnerCode'];
        $this->data['ggsLiveAuthToken']   = $show('ggs', 'AuthToken');
    }

    /**
     * The token to work with: what the admin typed, or the stored one when the field was left empty (the form never
     * shows a stored token, so an empty field means "keep it").
     */
    protected function token($field, $vendor, $storedField)
    {
        $value = trim((string)($this->formData[$field] ?? ''));
        if($value !== '')
        {
            return $value;
        }
        $credentials = (new AddonModuleRepository())->getModuleConfiguration()['credentials'] ?? [];

        return (string)($credentials[$vendor][$storedField] ?? '');
    }

    public function update()
    {
        $moduleRepository        = new AddonModuleRepository();
        $sslStoreLivePartnerCode = $this->formData['tssLivePartnerCode'];
        $sslStoreLiveAuthToken   = $this->token('tssLiveAuthToken', 'tss', 'AuthToken');
        $sslStoreTestPartnerCode = $this->formData['tssTestPartnerCode'];
        $sslStoreTestAuthToken   = $this->token('tssTestAuthToken', 'tss', 'TestAuthToken');
        $goGetSslLivePartnerCode = $this->formData['ggsLivePartnerCode'];
        $goGetSslLiveAuthToken   = $this->token('ggsLiveAuthToken', 'ggs', 'AuthToken');
        $operationMode           = $this->formData['OperationMode'];

        $test = $this->testConnection();
        if($test !== true) return $test;

        $moduleRepository->saveCredentials($sslStoreLivePartnerCode, $sslStoreLiveAuthToken, $goGetSslLivePartnerCode, $goGetSslLiveAuthToken, $sslStoreTestPartnerCode, $sslStoreTestAuthToken, $operationMode);

        $page = Request::get('mg-page');
        if($page === 'configuration')
        {
            return (new Response())->setSuccess($this->translate('successAPI'))->setActions([new Redirect(Url::route('',
                ['module' => 'TSSGGSModule', 'mg-page' => 'configuration', 'mg-action' => 'step5']
            ))]);
        }

    }

    public function testConnection()
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();

        // an empty token field means "keep the stored one", so the test uses what would be saved
        $sslStoreLivePartnerCode = $this->formData['tssLivePartnerCode'];
        $sslStoreLiveAuthToken   = $this->token('tssLiveAuthToken', 'tss', 'AuthToken');
        $sslStoreTestPartnerCode = $this->formData['tssTestPartnerCode'];
        $sslStoreTestAuthToken   = $this->token('tssTestAuthToken', 'tss', 'TestAuthToken');
        $operationMode           = $this->formData['OperationMode'];
        $goGetSslLivePartnerCode = $this->formData['ggsLivePartnerCode'];
        $goGetSslLiveAuthToken   = $this->token('ggsLiveAuthToken', 'ggs', 'AuthToken');

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