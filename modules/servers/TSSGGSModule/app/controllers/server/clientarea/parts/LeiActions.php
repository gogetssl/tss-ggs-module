<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\LeiCompanySearch;
use ModulesGarden\TSSGGSModule\App\Libs\LeiService;
use WHMCS\Database\Capsule;

/**
 * LEI codes: status page, order / renewal form, company search, order and refresh.
 * Part of the client area controller (clientarea\home).
 */
trait LeiActions
{
    /**
     * Service page: the order form while the service waits for configuration, else the LEI status
     */
    private function indexLeiHTML($input, $vars = [])
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !LeiService::isOrdered($serviceId))
        {
            return $this->leiConfigurationHTML($input, $vars);
        }

        $stored = LeiService::stored($serviceId);
        $vars['refreshError'] = '';
        if(LeiService::refreshDue($stored))
        {
            try
            {
                $stored = LeiService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $status = LeiService::displayStatus($stored);
        $tones  = ['active' => 'ok', 'processing' => 'wait', 'pending' => 'wait', 'user_confirmed' => 'wait', 'pre_issue' => 'wait',
                   'need_action' => 'warn', 'incomplete' => 'warn', 'unpaid' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'revoked' => 'off', 'expired' => 'off'];
        $entity = (array)($stored['legalEntity'] ?? []);
        $signer = (array)($stored['signingAuthority'] ?? []);

        $vars['leiStatus']       = $status;
        $vars['leiStatusText']   = $this->leiText('leiStatus_' . $status, ucfirst($status));
        $vars['leiTone']         = $tones[$status] ?? 'wait';
        $vars['leiNumber']       = (string)($stored['leiNumber'] ?? '');
        $vars['leiGleifUrl']     = $vars['leiNumber'] !== '' ? LeiService::gleifUrl($vars['leiNumber']) : '';
        $vars['leiLegalName']    = (string)($stored['legalName'] ?? Capsule::table('tblhosting')->where('id', $serviceId)->value('domain'));
        $vars['leiRegistration'] = (string)($entity['registration_authority_entity_id'] ?? '');
        $vars['leiJurisdiction'] = (string)($entity['legal_jurisdiction'] ?? '');
        // the data as submitted to the issuer (also what the client confirms)
        $vars['leiLegalAddress'] = $this->leiAddressText((array)($entity['legal_address'] ?? []));
        $vars['leiHqAddress']    = $this->leiAddressText((array)($entity['headquarters_address'] ?? []));
        $vars['leiRaCode']       = (string)($stored['registrationAuthorityId'] ?? '');
        $vars['leiNeedsConfirm'] = LeiService::needsConfirmation($stored);
        $vars['leiSigner']       = trim(($signer['first_name'] ?? '') . ' ' . ($signer['last_name'] ?? ''));
        $vars['leiIsRenewal']    = !empty($stored['isRenewal']);
        $vars['leiItemId']       = (int)($stored['itemId'] ?? 0);
        $vars['leiValidFrom']    = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['leiValidTill']    = $this->smimeDate($stored['validTill'] ?? '');
        $vars['leiOrderedAt']    = $this->smimeDate($stored['orderedAt'] ?? '');
        $vars                    = $this->gatewayRenewalVars(LeiService::class, $serviceId, $stored, $input, $vars);

        return ['tpl' => 'home_lei', 'vars' => $vars];
    }

    private function leiConfigurationHTML($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $client    = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();
        $context   = LeiService::orderContext($serviceId);
        $renewal   = $context['renewal'];
        $last      = (array)$context['last'];
        $entity    = (array)($last['legalEntity'] ?? []);
        $signer    = (array)($last['signingAuthority'] ?? []);
        $address   = (array)($entity['legal_address'] ?? []);
        $state     = $client && strcasecmp(trim((string)$client->state), 'None') !== 0 ? (string)$client->state : '';

        $vars['leiTerm']        = (int)$context['term'];
        $vars['leiRenewal']     = (bool)$renewal;
        $vars['leiRenewNumber'] = $renewal ? (string)$renewal['leiNumber'] : '';
        $vars['leiRenewName']   = $renewal ? (string)($renewal['legalName'] ?? '') : '';
        $vars['leiRenewTill']   = $renewal && !empty($renewal['validTill']) ? $this->smimeDate($renewal['validTill']) : '';
        $vars['leiSearch']      = true;
        $vars['leiOpenCorporates'] = LeiCompanySearch::token() !== '';
        // the form: the last order's data (an order again after a rejection), else the client profile
        $vars['leiForm'] = [
            'legal_name'         => (string)($entity['legal_name'] ?? ($client ? $client->companyname : '')),
            'registration'       => (string)($entity['registration_authority_entity_id'] ?? ''),
            'jurisdiction'       => (string)($entity['legal_jurisdiction'] ?? ($client ? $client->country : '')),
            'incorporation_date' => (string)($entity['incorporation_date'] ?? ''),
            'address_line_1'     => (string)($address['address_line_1'] ?? ($client ? $client->address1 : '')),
            'city'               => (string)($address['city'] ?? ($client ? $client->city : '')),
            'region'             => (string)($address['region'] ?? $state),
            'postal_code'        => (string)($address['postal_code'] ?? ($client ? $client->postcode : '')),
            'country'            => (string)($address['country'] ?? ($client ? $client->country : '')),
            'first_name'         => (string)($signer['first_name'] ?? ($client ? $client->firstname : '')),
            'last_name'          => (string)($signer['last_name'] ?? ($client ? $client->lastname : '')),
        ];
        $vars['smimeCountries'] = [];
        try
        {
            $vars['smimeCountries'] = (new \WHMCS\Utility\Country())->getCountryNameArray();
        }
        catch(\Throwable $e)
        {
        }

        return ['tpl' => 'home_lei_configuration', 'vars' => $vars];
    }

    /**
     * Order: a new LEI, or the renewal of the issued one (lei_mode = renew)
     */
    public function createLeiJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!LeiService::isLeiWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception($this->leiText('leiNotLeiProduct', 'This service is not an LEI code'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }

        $renewal = LeiService::orderContext($serviceId)['renewal'];
        $signing = LeiService::signingAuthority(is_array($input['signing'] ?? null) ? $input['signing'] : [], $error);
        if(!$signing)
        {
            throw new \Exception($this->leiFieldError($error));
        }
        if(empty($input['lei_confirm']))
        {
            throw new \Exception($this->leiText('leiConfirmRequired', 'Confirm that you are authorized to apply for the LEI of this legal entity.'));
        }

        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception($this->leiText('leiAlreadyOrdered', 'The LEI has already been ordered'));
        }
        try
        {
            if($renewal && ($input['lei_mode'] ?? '') === 'renew')
            {
                LeiService::renew($serviceId, !empty($input['level1_same']), $signing);
                $message = $this->leiText('leiRenewedSuccessfully', 'The renewal of your LEI has been ordered.');
            }
            else
            {
                $entity = LeiService::legalEntity(is_array($input['entity'] ?? null) ? $input['entity'] : [], $error);
                if(!$entity)
                {
                    throw new \Exception($this->leiFieldError($error));
                }
                LeiService::create($serviceId, $entity, $signing);
                $message = $this->leiText('leiOrderedSuccessfully', 'Your LEI has been ordered.');
            }
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Company search of the order form (OpenCorporates when configured, GLEIF always)
     */
    public function searchLeiCompanyJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        if(!LeiService::isLeiWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception($this->leiText('leiNotLeiProduct', 'This service is not an LEI code'));
        }

        return ['results' => LeiCompanySearch::search((string)($input['query'] ?? ''), (string)($input['country'] ?? ''))];
    }

    public function refreshLeiDetailsJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!LeiService::isOrdered($serviceId))
        {
            throw new \Exception($this->leiText('leiNotOrderedYet', 'The LEI has not been ordered yet'));
        }
        LeiService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('smimeDetailsRefreshed')];
    }

    /** the client confirms the submitted data the issuer waits for */
    public function confirmLeiJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!LeiService::isOrdered($serviceId))
        {
            throw new \Exception($this->leiText('leiNotOrderedYet', 'The LEI has not been ordered yet'));
        }
        if(LeiService::confirm($serviceId) === null)
        {
            throw new \Exception($this->leiText('leiNotWaitingConfirmation', 'The LEI issuer is not waiting for a confirmation any more. Reload the page to see the current status.'));
        }

        return ['success' => true, 'message' => $this->leiText('leiConfirmedMessage', 'Thank you. The LEI issuer continues with the registration.')];
    }

    /** "Legal name: this field is required." from [language key, field] */
    private function leiFieldError($error)
    {
        $field = $this->leiText('leiField_' . ($error[1] ?? ''), (string)($error[1] ?? ''));

        return sprintf($this->leiText($error[0] ?? 'leiFieldInvalid', '%s: the value is not valid.'), $field);
    }

    private function leiAddressText(array $address)
    {
        $parts = [];
        foreach(['address_line_1', 'postal_code', 'city', 'region', 'country'] as $field)
        {
            if(!empty($address[$field]))
            {
                $parts[] = (string)$address[$field];
            }
        }

        return implode(', ', $parts);
    }

    /** a client text of the LEI pages, with an English fallback when the language file has no such key */
    private function leiText($key, $default)
    {
        $text = Lang::absoluteT('serverCA', 'home', $key);

        return (!is_string($text) || $text === '' || strpos($text, $key) !== false) ? $default : $text;
    }
}
