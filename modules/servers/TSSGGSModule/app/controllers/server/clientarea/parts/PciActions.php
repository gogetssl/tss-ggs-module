<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\PciService;
use WHMCS\Database\Capsule;

/**
 * HackerGuardian PCI scanning: order form (organization, administrator), scan account page with the login of the scan
 * control centre, refresh. Part of the client area controller (clientarea\home).
 */
trait PciActions
{
    /**
     * Service page: the order form while the service waits for configuration, else the scan account
     */
    private function indexPciHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !PciService::isOrdered($serviceId))
        {
            return $this->pciConfigurationHTML($input, $vars);
        }

        $stored = PciService::stored($serviceId);
        $vars['refreshError'] = '';
        if(PciService::refreshDue($stored))
        {
            try
            {
                $stored = PciService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                $vars['refreshError'] = $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? 'processing'));
        $tones  = ['active' => 'ok', 'processing' => 'wait', 'pending' => 'wait', 'incomplete' => 'warn', 'unpaid' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'refunded' => 'off', 'expired' => 'off'];
        $admin  = (array)($stored['contacts']['administrator'] ?? []);

        $vars['pciStatus']        = $status;
        $vars['pciStatusText']    = $this->smimeStatusText($status);
        $vars['pciTone']          = $tones[$status] ?? 'wait';
        $vars['pciOrganization']  = (string)($stored['organization'] ?? Capsule::table('tblhosting')->where('id', $serviceId)->value('domain'));
        $vars['pciAdministrator'] = $admin ? trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')) . (!empty($admin['email']) ? ' (' . $admin['email'] . ')' : '') : '';
        // the login is shown while the account runs; a cancelled or expired account has none to use
        $vars['pciLogin']         = $status === 'active' ? PciService::login($serviceId) : [];
        $vars['pciLoginUrl']      = PciService::LOGIN_URL;
        $vars['pciItemId']        = (int)($stored['itemId'] ?? 0);
        $vars['pciVendorOrderId'] = (string)($stored['vendorOrderId'] ?? '');
        $vars['pciValidFrom']     = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['pciValidTill']     = $this->smimeDate($stored['validTill'] ?? '');
        $vars['pciOrderedAt']     = $this->smimeDate($stored['orderedAt'] ?? '');
        $vars                     = $this->gatewayRenewalVars(PciService::class, $serviceId, $stored, $input, $vars);

        return ['tpl' => 'home_pci', 'vars' => $vars];
    }

    private function pciConfigurationHTML($input, $vars)
    {
        $client = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();

        $phone = $client ? trim((string)$client->phonenumber) : '';
        if($phone !== '' && strpos($phone, '+') !== 0 && !empty($client->phonecc))
        {
            $phone = '+' . ltrim((string)$client->phonecc, '+') . '.' . $phone;
        }

        $vars['agreement']   = CertificateService::agreement((int)$input['params']['pid']);
        $vars['smimeClient'] = [
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

        return ['tpl' => 'home_pci_configuration', 'vars' => $vars];
    }

    public function createPciJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!PciService::isPciWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception($this->pciText('pciNotPciProduct', 'This service is not a HackerGuardian PCI scan.'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }
        if(CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        $contacts = PciService::contacts(
            is_array($input['org'] ?? null) ? $input['org'] : [],
            is_array($input['admin'] ?? null) ? $input['admin'] : [],
            $missing
        );
        if($missing)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('serverCA', 'home', ($missing[0] === 'org' ? 'dsOrg_' : 'dsAdmin_') . $missing[1])));
        }
        if(filter_var($contacts['administrator']['email'], FILTER_VALIDATE_EMAIL) === false)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($contacts['administrator']['email'])));
        }

        // a double submit must not place two orders
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception($this->pciText('pciAlreadyOrdered', 'The scan account has already been ordered.'));
        }
        try
        {
            PciService::create($serviceId, $contacts);
        }
        catch(\Exception $e)
        {
            // the organization name is taken at the scanning service (also after a refund): say so in the client's words
            if(PciService::isNameTaken($e->getMessage()))
            {
                throw new \Exception($this->pciText('pciNameTaken', 'An organization with this name already has a scan account. Check the name, or contact support.'));
            }
            throw $e;
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => $this->pciText('pciOrderedSuccessfully', 'Your scan account has been created.')];
    }

    public function refreshPciDetailsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!PciService::isOrdered($serviceId))
        {
            throw new \Exception($this->pciText('pciNotOrderedYet', 'The scan account has not been ordered yet.'));
        }
        PciService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('smimeDetailsRefreshed')];
    }

    /** a client text of the PCI pages, with an English fallback when the language file has no such key */
    private function pciText($key, $default)
    {
        $text = Lang::absoluteT('serverCA', 'home', $key);

        return (!is_string($text) || $text === '' || strpos($text, $key) !== false) ? $default : $text;
    }
}
