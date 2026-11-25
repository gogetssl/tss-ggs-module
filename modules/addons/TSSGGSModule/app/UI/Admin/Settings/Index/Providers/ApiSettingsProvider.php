<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\CrossVendor;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
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
        $stored        = (new AddonModuleRepository())->storedCredentials();

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

        // a vendor that is not connected keeps its stored credentials: its partner code is shown, so the admin only
        // switches Connect to Yes (the stored token is kept, see token())
        foreach(['ggs' => ['ggsLivePartnerCode' => 'PartnerCode'], 'tss' => ['tssLivePartnerCode' => 'PartnerCode', 'tssTestPartnerCode' => 'TestPartnerCode', 'OperationMode' => 'OperationMode']] as $vendor => $fields)
        {
            foreach($fields as $field => $storedField)
            {
                if((string)($this->data[$field] ?? '') === '' && (string)($stored[$vendor][$storedField] ?? '') !== '')
                {
                    $this->data[$field] = $stored[$vendor][$storedField];
                }
            }
        }
        $this->data['ggsLiveAuthToken']   = $show('ggs', 'AuthToken');

        // the other vendor's products (Settings > API, "Connect ... products")
        $this->data['connectOther'] = Edition::connectedVendor() !== '';
    }

    /** Whether the form asks for the other vendor to be connected (no switch on the page: as it is now) */
    protected function connectChosen()
    {
        if(!array_key_exists('connectOther', $this->formData->toArray()))
        {
            return Edition::connectedVendor() !== '';
        }
        $value = $this->formData['connectOther'];

        return !empty($value) && !in_array($value, ['off', 'false', '0'], true);
    }

    /** Vendors the saved credentials are for: the edition's own, plus the other one when it is connected */
    protected function chosenVendors()
    {
        return $this->connectChosen() ? [Edition::primaryVendor(), Edition::otherVendor()] : [Edition::primaryVendor()];
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
        $credentials = (new AddonModuleRepository())->storedCredentials();

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

        // a connected vendor that still sells products in WHMCS can not be disconnected: its services need its API
        $connect = $this->connectChosen();
        if(!$connect && Edition::connectedVendor() !== '' && CrossVendor::productCount(Edition::connectedVendor()) > 0)
        {
            return (new Response())->setError(sprintf($this->translate('disconnectHasProducts'), Edition::brandOf(Edition::connectedVendor()), CrossVendor::productCount(Edition::connectedVendor())));
        }

        // every API in use is tested first; nothing is saved while one of them fails
        $test = $this->testConnection(true);
        if($test instanceof Response) return $test;

        // connected first, so the other vendor's credentials are saved with the edition's own ones
        Edition::setConnectedVendor($connect ? Edition::otherVendor() : '');

        $moduleRepository->saveCredentials($sslStoreLivePartnerCode, $sslStoreLiveAuthToken, $goGetSslLivePartnerCode, $goGetSslLiveAuthToken, $sslStoreTestPartnerCode, $sslStoreTestAuthToken, $operationMode);

        $page = Request::get('mg-page');
        if($page === 'configuration')
        {
            return (new Response())->setSuccess($this->translate('successAPI') . '<br>' . implode('<br>', $test))->setActions([new Redirect(Url::route('',
                ['module' => 'TSSGGSModule', 'mg-page' => 'configuration', 'mg-action' => 'step5']
            ))]);
        }

        return (new Response())->setSuccess($this->translate('savedAPI') . '<br>' . implode('<br>', $test));
    }

    /**
     * Tests every API in use (the edition's own, plus the other vendor while Connect is on) and reports each one on
     * its own line, e.g. "TheSSLStore API (sandbox): connected" / "GoGetSSL API: connection failed ...".
     *
     * @param bool $saving true when update() calls it: the result lines (array) when all APIs work, else the error
     *                    Response; the Test Connection button (false) always gets a Response and is also told when the
     *                    form holds changes that are not saved yet (Connect switched, a token typed)
     * @return Response|string[]
     */
    public function testConnection($saving = false)
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

        // the edition's own vendor first
        $lines  = [];
        $failed = false;
        foreach($this->chosenVendors() as $vendor)
        {
            $label = Edition::brandOf($vendor) . ' API';
            if($vendor === 'tss' && $operationMode == 'sandbox')
            {
                // the sandbox pair is tested against the sandbox gateway, the one the module then uses
                $api    = new SSLTrustCenterApi('TSS', $sslStoreTestPartnerCode, $sslStoreTestAuthToken, 'tls', true);
                $label .= ' (' . $this->translate('apiSandbox') . ')';
            }
            elseif($vendor === 'tss')
            {
                $api = new SSLTrustCenterApi('TSS', $sslStoreLivePartnerCode, $sslStoreLiveAuthToken);
            }
            else
            {
                $api = new SSLTrustCenterApi('GGS', $goGetSslLivePartnerCode, $goGetSslLiveAuthToken);
            }

            // every API is checked (one working API must not hide a failing one)
            try
            {
                $api->validate();
                $lines[] = '&#10004; ' . $label . ': ' . $this->translate('apiConnected');
            }
            catch(\Throwable $e)
            {
                $failed  = true;
                $lines[] = '&#10008; ' . $label . ': ' . $this->translate('apiFailed');
            }
        }

        if($failed)
        {
            return (new Response())->setError(implode('<br>', $lines) . ($saving ? '<br>' . $this->translate('notSavedAPI') : ''));
        }
        if($saving)
        {
            return $lines;
        }

        return (new Response())->setSuccess(implode('<br>', $lines) . ($this->unsaved() ? '<br>' . $this->translate('testUnsaved') : ''));
    }

    /** Whether the form differs from what is stored: Connect switched or a token typed */
    protected function unsaved()
    {
        if($this->connectChosen() !== (Edition::connectedVendor() !== ''))
        {
            return true;
        }
        foreach(['tssLiveAuthToken', 'tssTestAuthToken', 'ggsLiveAuthToken'] as $field)
        {
            if(trim((string)($this->formData[$field] ?? '')) !== '')
            {
                return true;
            }
        }

        return false;
    }
}
