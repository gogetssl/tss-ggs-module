<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\DocumentSigningService;
use WHMCS\Database\Capsule;

/**
 * Document signing certificates: status page, order form, order and refresh.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait DocumentSigningActions
{
    /* ---------------- Document signing (TheSSLStore, /certificates/ds) ---------------- */

    /**
     * Service page: the order form while the service waits for configuration, else the order status
     */
    private function indexDocumentSigningHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !DocumentSigningService::isOrdered($serviceId))
        {
            return $this->documentSigningConfigurationHTML($input, $vars);
        }

        $stored = DocumentSigningService::stored($serviceId);
        $vars['refreshError'] = '';
        if(DocumentSigningService::refreshDue($stored))
        {
            try
            {
                $stored = DocumentSigningService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? 'processing'));
        $tones  = ['active' => 'ok', 'invited' => 'wait', 'processing' => 'wait', 'pending' => 'wait', 'unpaid' => 'warn', 'incomplete' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'revoked' => 'off', 'refunded' => 'off', 'expired' => 'off'];

        $vars['dsStatus']        = $status;
        $vars['dsStatusText']    = $this->smimeStatusText($status);
        $vars['dsTone']          = $tones[$status] ?? 'wait';
        $vars['dsMethod']        = (string)($stored['method'] ?? '');
        $vars['dsDelivery']      = $this->documentSigningDeliveryText($stored);
        $vars['dsOrganization']  = (string)($stored['organization'] ?? Capsule::table('tblhosting')->where('id', $serviceId)->value('domain'));
        $vars['dsItemId']        = (int)($stored['itemId'] ?? 0);
        $vars['dsVendorOrderId'] = (string)($stored['vendorOrderId'] ?? '');
        $vars['dsValidFrom']     = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['dsValidTill']     = $this->smimeDate($stored['validTill'] ?? '');
        $vars['dsOrderedAt']     = $this->smimeDate($stored['orderedAt'] ?? '');
        // the vendor's pages: enrolment while the order waits for the client, token set-up once the certificate is there
        $vars['dsEnrollmentLink'] = $status === 'invited' ? (string)($stored['enrollmentLink'] ?? '') : '';
        $vars['dsTokenSetupLink'] = $status === 'active' ? (string)($stored['tokenSetupLink'] ?? '') : '';
        $vars                     = $this->gatewayRenewalVars(DocumentSigningService::class, $serviceId, $stored, $input, $vars);

        return ['tpl' => 'home_docsigning', 'vars' => $vars];
    }

    private function documentSigningConfigurationHTML($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $client    = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();

        $vars['dsError'] = '';
        $available       = DocumentSigningService::availableDelivery($serviceId);
        $methods   = [];
        foreach($available['methods'] as $method)
        {
            $methods[$method] = Lang::absoluteT('serverCA', 'home', $method === DocumentSigningService::METHOD_SHIP_TOKEN ? 'dsMethod_ship' : 'dsMethod_existing');
        }

        $previous = (array)(DocumentSigningService::stored($serviceId)['previous'] ?? []);
        $last     = $previous ? (array)end($previous) : [];
        $lastTill = !empty($last['validTill']) ? strtotime($last['validTill']) : false;

        $phone = $client ? trim((string)$client->phonenumber) : '';
        if($phone !== '' && strpos($phone, '+') !== 0 && !empty($client->phonecc))
        {
            $phone = '+' . ltrim((string)$client->phonecc, '+') . '.' . $phone;
        }

        // a renewal preselects the delivery of the order before it
        $vars['dsPreviousMethod']  = (string)($last['method'] ?? '');
        $vars['dsMethods']         = $methods;
        $vars['dsShippingOptions'] = $available['shipping_options'];
        $vars['dsModels']          = $available['models'];
        $vars['dsTerm']            = (int)$available['term'];
        $vars['dsRenewal']         = (bool)$previous;
        // renewal: the certificate ordered before stays valid until its end date
        $vars['dsCurrentValidTill'] = $lastTill && $lastTill > time() && ($last['status'] ?? '') === 'active' ? date('F j, Y', $lastTill) : '';
        $vars['agreement']          = CertificateService::agreement((int)$input['params']['pid']);
        $vars['smimeClient']        = [
            'email'       => $client ? $client->email : '',
            'firstname'   => $client ? $client->firstname : '',
            'lastname'    => $client ? $client->lastname : '',
            'companyname' => $client ? $client->companyname : '',
            'phonenumber' => $phone,
            'country'     => $client ? $client->country : '',
            'state'       => ($client && strcasecmp(trim((string)$client->state), 'None') !== 0) ? $client->state : '',
            'city'        => $client ? $client->city : '',
            'postcode'    => $client ? $client->postcode : '',
            'address1'    => $client ? $client->address1 : '',
            'address2'    => $client ? $client->address2 : '',
        ];
        $vars['smimeCountries'] = [];
        try
        {
            $vars['smimeCountries'] = (new \WHMCS\Utility\Country())->getCountryNameArray();
        }
        catch(\Throwable $e)
        {
        }

        return ['tpl' => 'home_docsigning_configuration', 'vars' => $vars];
    }

    public function createDocumentSigningJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!DocumentSigningService::isDocumentSigningWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'dsNotDocumentSigningProduct'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }
        if(CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        // the delivery: a shipped token (with its own address, else the organization's) or the client's own token
        try
        {
            $provisioning = DocumentSigningService::provisioning([
                'method'   => (string)($input['ds_method'] ?? ''),
                'model'    => (string)($input['ds_model'] ?? ''),
                'shipping' => (string)($input['ds_shipping'] ?? ''),
            ], is_array($input['ship'] ?? null) ? $input['ship'] : []);
        }
        catch(\Exception $e)
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', $e->getMessage()));
        }

        $contacts = DocumentSigningService::contacts(
            is_array($input['org'] ?? null) ? $input['org'] : [],
            is_array($input['admin'] ?? null) ? $input['admin'] : [],
            $missing
        );
        if($missing)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('serverCA', 'home', ($missing[0] === 'org' ? 'smimeOrg_' : 'smimeAdmin_') . $missing[1])));
        }
        if(filter_var($contacts['administrator']['email'], FILTER_VALIDATE_EMAIL) === false)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($contacts['administrator']['email'])));
        }

        // a double submit must not place two orders
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'dsAlreadyOrdered'));
        }
        try
        {
            DocumentSigningService::create($serviceId, ['provisioning' => $provisioning, 'contacts' => $contacts]);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => Lang::absoluteT('serverCA', 'home', 'dsOrderedSuccessfully')];
    }

    public function refreshDocumentSigningDetailsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!DocumentSigningService::isOrdered($serviceId))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'dsNotOrderedYet'));
        }
        DocumentSigningService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('smimeDetailsRefreshed')];
    }

    /** "A new token, standard shipping" / "Your own token (SafeNet eToken 5110)" */
    private function documentSigningDeliveryText(array $stored)
    {
        $method = (string)($stored['method'] ?? '');
        if($method === DocumentSigningService::METHOD_EXISTING_TOKEN)
        {
            $model = strtoupper((string)($stored['tokenModel'] ?? ''));
            $text  = Lang::absoluteT('serverCA', 'home', 'dsMethod_existing');

            return isset(DocumentSigningService::TOKEN_MODELS[$model]) ? $text . ' (' . DocumentSigningService::TOKEN_MODELS[$model] . ')' : $text;
        }
        if($method === DocumentSigningService::METHOD_SHIP_TOKEN)
        {
            return Lang::absoluteT('serverCA', 'home', 'dsMethod_ship');
        }

        return '';
    }
}
