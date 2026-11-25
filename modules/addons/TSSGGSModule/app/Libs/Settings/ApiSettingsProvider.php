<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Settings;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\CrossVendor;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Migration\Credentials;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * Settings > API: the partner codes and auth tokens of the vendor accounts, and the switch that connects the other
 * vendor's products. Saving tests every API in use first; nothing is saved while one of them fails.
 */
class ApiSettingsProvider extends Provider
{
    const TEXTS = 'admin.settings.index.providers.api_settings_provider';

    /** the token fields of the screen */
    const TOKENS = ['tssLiveAuthToken', 'tssTestAuthToken', 'ggsLiveAuthToken'];

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
        $this->data['ggsLiveAuthToken'] = $show('ggs', 'AuthToken');
        if((string)$this->data['OperationMode'] === '')
        {
            $this->data['OperationMode'] = 'live';
        }

        // the other vendor's products ("Connect ... products")
        $this->data['connectOther'] = Edition::connectedVendor() !== '';

        $this->availableValues['OperationMode'] = ['live' => 'Live', 'sandbox' => 'Sandbox'];
    }

    public function update()
    {
        // a connected vendor that still sells products in WHMCS can not be disconnected: its services need its API
        $connect = $this->connectChosen();
        if(!$connect && Edition::connectedVendor() !== '' && CrossVendor::productCount(Edition::connectedVendor()) > 0)
        {
            return (new Result())->setError(sprintf($this->translate('disconnectHasProducts'), Edition::brandOf(Edition::connectedVendor()), CrossVendor::productCount(Edition::connectedVendor())));
        }

        // every API in use is tested first; nothing is saved while one of them fails
        [$lines, $failed] = $this->check();
        if($failed)
        {
            return (new Result())->setError(implode('<br>', $lines) . '<br>' . $this->translate('notSavedAPI'));
        }

        // connected first, so the other vendor's credentials are saved with the edition's own ones
        Edition::setConnectedVendor($connect ? Edition::otherVendor() : '');

        (new AddonModuleRepository())->saveCredentials(
            (string)$this->formData['tssLivePartnerCode'],
            $this->token('tssLiveAuthToken', 'tss', 'AuthToken'),
            (string)$this->formData['ggsLivePartnerCode'],
            $this->token('ggsLiveAuthToken', 'ggs', 'AuthToken'),
            (string)$this->formData['tssTestPartnerCode'],
            $this->token('tssTestAuthToken', 'tss', 'TestAuthToken'),
            (string)$this->formData['OperationMode']
        );

        return (new Result())->setSuccess($this->translate('savedAPI') . '<br>' . implode('<br>', $lines));
    }

    /**
     * The Test Connection button: every API in use is tested with what the form holds, each on its own line, e.g.
     * "TheSSLStore API (sandbox): connected" / "GoGetSSL API: connection failed ...". Nothing is saved; the admin is
     * told so when the form holds changes (Connect switched, a token typed).
     */
    public function test(): Result
    {
        [$lines, $failed] = $this->check();
        if($failed)
        {
            return (new Result())->setError(implode('<br>', $lines));
        }

        return (new Result())->setSuccess(implode('<br>', $lines) . ($this->unsaved() ? '<br>' . $this->translate('testUnsaved') : ''));
    }

    /**
     * Tests the edition's own API, plus the other vendor's while Connect is on. An empty token field means "keep the
     * stored one", so the test uses what would be saved. Every API is checked (one working API must not hide a
     * failing one).
     *
     * @return array [string[] one line per API (simple HTML), bool whether one of them failed]
     */
    protected function check(): array
    {
        $lines  = [];
        $failed = false;
        foreach($this->chosenVendors() as $vendor)
        {
            $label = htmlspecialchars(Edition::brandOf($vendor)) . ' API';
            if($vendor === 'tss' && $this->formData['OperationMode'] == 'sandbox')
            {
                // the sandbox pair is tested against the sandbox gateway, the one the module then uses
                $api    = new SSLTrustCenterApi('TSS', (string)$this->formData['tssTestPartnerCode'], $this->token('tssTestAuthToken', 'tss', 'TestAuthToken'), 'tls', true);
                $label .= ' (' . htmlspecialchars($this->translate('apiSandbox')) . ')';
            }
            elseif($vendor === 'tss')
            {
                $api = new SSLTrustCenterApi('TSS', (string)$this->formData['tssLivePartnerCode'], $this->token('tssLiveAuthToken', 'tss', 'AuthToken'));
            }
            else
            {
                $api = new SSLTrustCenterApi('GGS', (string)$this->formData['ggsLivePartnerCode'], $this->token('ggsLiveAuthToken', 'ggs', 'AuthToken'));
            }

            try
            {
                $api->validate();
                $lines[] = '&#10004; ' . $label . ': ' . htmlspecialchars($this->translate('apiConnected'));
            }
            catch(\Throwable $e)
            {
                $failed  = true;
                $lines[] = '&#10008; ' . $label . ': ' . htmlspecialchars($this->translate('apiFailed'));
            }
        }

        return [$lines, $failed];
    }

    /** Whether the form asks for the other vendor to be connected (no switch on the page: as it is now) */
    protected function connectChosen(): bool
    {
        if(!array_key_exists('connectOther', $this->formData->toArray()))
        {
            return Edition::connectedVendor() !== '';
        }
        $value = $this->formData['connectOther'];

        return !empty($value) && !in_array($value, ['off', 'false', '0'], true);
    }

    /** Vendors the saved credentials are for: the edition's own, plus the other one when it is connected */
    protected function chosenVendors(): array
    {
        return $this->connectChosen() ? [Edition::primaryVendor(), Edition::otherVendor()] : [Edition::primaryVendor()];
    }

    /**
     * The token to work with: what the admin typed, or the stored one when the field was left empty (the screen never
     * shows a stored token, so an empty field means "keep it").
     */
    protected function token(string $field, string $vendor, string $storedField): string
    {
        $value = trim((string)($this->formData[$field] ?? ''));
        if($value !== '')
        {
            return $value;
        }
        $credentials = (new AddonModuleRepository())->storedCredentials();

        return (string)($credentials[$vendor][$storedField] ?? '');
    }

    /** Whether the form differs from what is stored: Connect switched or a token typed */
    protected function unsaved(): bool
    {
        if($this->connectChosen() !== (Edition::connectedVendor() !== ''))
        {
            return true;
        }
        foreach(self::TOKENS as $field)
        {
            if(trim((string)($this->formData[$field] ?? '')) !== '')
            {
                return true;
            }
        }

        return false;
    }
}
