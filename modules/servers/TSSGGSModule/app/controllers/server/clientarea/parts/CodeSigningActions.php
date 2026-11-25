<?php

namespace TSSGGSModule\Server\app\controllers\server\clientarea\parts;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\Order;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\core\Lang;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\ProductLines\CodeSigningService;
use WHMCS\Database\Capsule;

/**
 * Code signing certificates: status page, order form, order and refresh.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait CodeSigningActions
{
    /* ---------------- Code signing (GoGetSSL, /certificates/cs) ---------------- */

    /**
     * Code signing service page: the order form while the service waits for configuration, else the order status
     */
    private function indexCodeSigningHTML($input, $vars = [])
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !CodeSigningService::isOrdered($serviceId))
        {
            return $this->codeSigningConfigurationHTML($input, $vars);
        }

        $stored = CodeSigningService::stored($serviceId);
        $vars['refreshError'] = '';
        if(CodeSigningService::refreshDue($stored))
        {
            try
            {
                $stored = CodeSigningService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                // a migrated invite order can not be read before the client enrols
                $vars['refreshError'] = ($stored['status'] ?? '') === 'invited' ? '' : $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? 'processing'));
        $tones  = ['active' => 'ok', 'invited' => 'wait', 'processing' => 'wait', 'pending' => 'wait', 'unpaid' => 'warn', 'incomplete' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'revoked' => 'off', 'refunded' => 'off', 'expired' => 'off'];

        $vars['csStatus']        = $status;
        $csText                  = Lang::absoluteT('serverCA', 'home', 'csStatus_' . $status);
        $vars['csStatusText']    = is_string($csText) && $csText !== '' && strpos($csText, 'csStatus_') === false ? $csText : $this->smimeStatusText($status);
        $vars['csTone']          = $tones[$status] ?? 'wait';
        $vars['csMethod']        = (string)($stored['method'] ?? '');
        $vars['csDelivery']      = $this->codeSigningDeliveryText($stored, CodeSigningService::isTssSectigo(CodeSigningService::remoteIdOfProduct((int)$input['params']['pid'])));
        $vars['csOrganization']  = (string)($stored['organization'] ?? Capsule::table('tblhosting')->where('id', $serviceId)->value('domain'));
        $vars['csItemId']        = (int)($stored['itemId'] ?? 0);
        $vars['csVendorOrderId'] = (string)($stored['vendorOrderId'] ?? '');
        $vars['csValidFrom']     = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['csValidTill']     = $this->smimeDate($stored['validTill'] ?? '');
        $vars['csOrderedAt']     = $this->smimeDate($stored['orderedAt'] ?? '');
        $vars['csTokenInitCode'] = (string)($stored['tokenInitCode'] ?? '');
        // migrated from the legacy TheSSLStore module: enrolment page (invite order) and DigiCert token set-up page
        $vars['csEnrollmentLink'] = $status === 'invited' ? (string)($stored['enrollmentLink'] ?? '') : '';
        $vars['csTokenSetupLink'] = $status === 'active' ? (string)($stored['tokenSetupLink'] ?? '') : '';
        $vars                    = $this->gatewayRenewalVars(CodeSigningService::class, $serviceId, $stored, $input, $vars);

        return ['tpl' => 'home_codesigning', 'vars' => $vars];
    }

    private function codeSigningConfigurationHTML($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $client    = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();

        $vars['csError'] = '';
        try
        {
            $available = CodeSigningService::availableDelivery($serviceId);
        }
        catch(\Exception $e)
        {
            $available       = ['methods' => [], 'fixed' => null, 'shipping_options' => [], 'renewal' => false, 'hsm_attestation' => false];
            $vars['csError'] = Lang::absoluteT($e->getMessage());
        }

        $previous = (array)(CodeSigningService::stored($serviceId)['previous'] ?? []);
        $last     = $previous ? (array)end($previous) : [];
        $lastTill = !empty($last['validTill']) ? strtotime($last['validTill']) : false;

        $methods = [];
        foreach($available['methods'] as $method)
        {
            $methods[$method] = Lang::absoluteT('serverCA', 'home', $this->codeSigningMethodKey($method, !empty($available['hsm_attestation'])));
        }
        $shippingLabel = '';
        if($available['fixed'] && $available['fixed']['method'] === CodeSigningService::METHOD_SHIP_TOKEN)
        {
            foreach($available['shipping_options'] as $option)
            {
                if($option['id'] === $available['fixed']['shipping'])
                {
                    $shippingLabel = $option['label'];
                }
            }
            $shippingLabel = $shippingLabel ?: ucfirst(strtolower((string)$available['fixed']['shipping'])) . ' Shipping';
        }

        $phone = $client ? trim((string)$client->phonenumber) : '';
        if($phone !== '' && strpos($phone, '+') !== 0 && !empty($client->phonecc))
        {
            $phone = '+' . ltrim((string)$client->phonecc, '+') . '.' . $phone;
        }

        $vars['csMethods']             = $methods;
        $vars['csFixedMethod']         = $available['fixed'] ? $available['fixed']['method'] : '';
        // renewal: the delivery used for the previous certificate is preselected when it is still offered
        $vars['csPreviousMethod']      = isset($methods[$last['method'] ?? '']) ? $last['method'] : '';
        $vars['csFixedLabel']          = $available['fixed'] ? Lang::absoluteT('serverCA', 'home', $this->codeSigningMethodKey($available['fixed']['method'], !empty($available['hsm_attestation']))) : '';
        $vars['csShippingLabel']       = $shippingLabel;
        $vars['csRenewal']             = $available['renewal'];
        $vars['csCurrentValidTill']    = $lastTill && $lastTill > time() && ($last['status'] ?? '') === 'active' ? date('F j, Y', $lastTill) : '';
        $vars['csTokenModels']         = CodeSigningService::TOKEN_MODELS;
        // TheSSLStore Sectigo/Comodo: the HSM model and the key attestation with the CSR
        $vars['csHsmModels']           = !empty($available['hsm_attestation']) ? CodeSigningService::HSM_MODELS : [];
        $vars['agreement']             = CertificateService::agreement((int)$input['params']['pid']);
        $vars['csClient']              = [
            'email'       => $client ? $client->email : '',
            'firstname'   => $client ? $client->firstname : '',
            'lastname'    => $client ? $client->lastname : '',
            'companyname' => ($last['organization'] ?? '') ?: ($client ? $client->companyname : ''),
            'phonenumber' => $phone,
            'country'     => $client ? $client->country : '',
            'state'       => ($client && strcasecmp(trim((string)$client->state), 'None') !== 0) ? $client->state : '',
            'city'        => $client ? $client->city : '',
            'postcode'    => $client ? $client->postcode : '',
            'address1'    => $client ? $client->address1 : '',
            'address2'    => $client ? $client->address2 : '',
        ];
        $vars['csCountries'] = [];
        try
        {
            $vars['csCountries'] = (new \WHMCS\Utility\Country())->getCountryNameArray();
        }
        catch(\Throwable $e)
        {
        }

        return ['tpl' => 'home_codesigning_configuration', 'vars' => $vars];
    }

    public function createCodeSigningJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!CodeSigningService::isCodeSigningWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('csNotCodeSigningProduct'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }
        if(CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        try
        {
            $order = CodeSigningService::buildOrder($serviceId, [
                'method'        => (string)($input['cs_method'] ?? ''),
                'model'         => (string)($input['cs_model'] ?? ''),
                'csr'           => (string)($input['cs_csr'] ?? ''),
                'hsm_model'     => (string)($input['cs_hsm_model'] ?? ''),
                'attestation'   => (string)($input['cs_attestation'] ?? ''),
                'organization'  => is_array($input['org'] ?? null) ? $input['org'] : [],
                'administrator' => is_array($input['admin'] ?? null) ? $input['admin'] : [],
                'shipping'      => is_array($input['ship'] ?? null) ? $input['ship'] : [],
            ]);
        }
        catch(\TSSGGSModule\App\Libs\ProductLines\CodeSigningInputException $e)
        {
            $argument = (string)$e->argument;
            $argument = strpos($argument, 'cs') === 0 ? Lang::absoluteT('serverCA', 'home', $argument) : htmlspecialchars($argument);
            throw new \Exception(sprintf(Lang::absoluteT($e->getMessage()), $argument));
        }

        // a double submit must not place two orders
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('csAlreadyOrdered'));
        }
        try
        {
            CodeSigningService::create($serviceId, $order);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => Lang::absoluteT('csOrderedSuccessfully')];
    }

    public function refreshCodeSigningDetailsJSON($input, $vars = [])
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!CodeSigningService::isOrdered($serviceId))
        {
            throw new \Exception(Lang::absoluteT('csNotOrderedYet'));
        }
        CodeSigningService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('csDetailsRefreshed')];
    }

    /**
     * Language key of a delivery method (TheSSLStore Sectigo/Comodo: an existing token is installed like an own HSM)
     */
    private function codeSigningMethodKey($method, $tssSectigo)
    {
        return $method === CodeSigningService::METHOD_HSM && $tssSectigo ? 'csMethod_hsm_token' : 'csMethod_' . str_replace('-', '_', (string)$method);
    }

    private function codeSigningDeliveryText(array $stored, $tssSectigo = false)
    {
        $method = (string)($stored['method'] ?? '');
        $text   = $method !== '' ? Lang::absoluteT('serverCA', 'home', $this->codeSigningMethodKey($method, $tssSectigo)) : '';
        if($method === CodeSigningService::METHOD_SHIP_TOKEN && !empty($stored['shipping']))
        {
            $text .= ' (' . ucfirst(strtolower((string)$stored['shipping'])) . ')';
        }
        elseif($method === CodeSigningService::METHOD_EXISTING_TOKEN && !empty($stored['tokenModel']))
        {
            $text .= ' (' . (CodeSigningService::TOKEN_MODELS[$stored['tokenModel']] ?? $stored['tokenModel']) . ')';
        }
        elseif($method === CodeSigningService::METHOD_HSM && !empty($stored['tokenModel']))
        {
            $text .= ' (' . (CodeSigningService::HSM_MODELS[$stored['tokenModel']] ?? $stored['tokenModel']) . ')';
        }

        return $text;
    }
}
