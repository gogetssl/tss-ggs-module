<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Order;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Product;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Config;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\Domain;
use ModulesGarden\Servers\TSSGGSModule\app\repository\whmcs\ProductConfigOptions;
use ModulesGarden\Servers\TSSGGSModule\app\services\InvoiceRenew;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepThree;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\Servers\TSSGGSModule\core\process\AbstractController;
use ModulesGarden\Servers\TSSGGSModule\Server;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AisHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateBundle;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateSyncHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Libs\SmimeService;
use ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService;
use ModulesGarden\TSSGGSModule\App\Libs\SslProductHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscriptionDomain;
use ModulesGarden\TSSGGSModule\App\Models\AisSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;
use WHMCS\Billing\Tax;
use WHMCS\Database\Capsule;

/**
 * S/MIME certificates: status page, order form, order and refresh.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait SmimeActions
{
    /* ---------------- S/MIME (GoGetSSL, /certificates/smime) ---------------- */

    /**
     * S/MIME service page: the order form while the service waits for configuration, else the order status
     */
    private function indexSmimeHTML($input, $vars = array())
    {
        $serviceId = (int)$input['params']['serviceid'];
        $ssl       = SSL::getByServiceId($serviceId);
        if(!$ssl)
        {
            throw new \Exception(Lang::absoluteT('smimeNotInitialized'));
        }

        $vars = $this->smimeBaseVars($input, $vars);

        if($ssl->status === 'Awaiting Configuration' && !SmimeService::isOrdered($serviceId))
        {
            return $this->smimeConfigurationHTML($input, $vars);
        }

        $stored = SmimeService::stored($serviceId);
        $vars['refreshError'] = '';
        if(SmimeService::refreshDue($stored))
        {
            try
            {
                $stored = SmimeService::refresh($serviceId);
            }
            catch(\Exception $e)
            {
                // an invite order (migrated) can not be read before the client enrols: no error for that
                $vars['refreshError'] = ($stored['status'] ?? '') === 'invited' ? '' : $e->getMessage();
            }
        }

        $status = strtolower((string)($stored['status'] ?? 'processing'));
        $emails = (array)($stored['emails'] ?? []);
        $tones  = ['active' => 'ok', 'invited' => 'wait', 'processing' => 'wait', 'pending' => 'wait', 'unpaid' => 'warn', 'incomplete' => 'warn',
                   'failed' => 'bad', 'rejected' => 'bad', 'cancelled' => 'off', 'revoked' => 'off', 'refunded' => 'off', 'expired' => 'off'];

        $vars['smimeStatus']        = $status;
        $vars['smimeStatusText']    = $this->smimeStatusText($status);
        $vars['smimeTone']          = $tones[$status] ?? 'wait';
        $vars['smimeEmails']        = $emails;
        $vars['smimeSubject']       = $emails ? $emails[0]['email'] : (string)Capsule::table('tblhosting')->where('id', $serviceId)->value('domain');
        $vars['smimeAllApproved']   = $emails && !array_filter($emails, function($e) { return empty($e['approved']); });
        $vars['smimeOrderId']       = (int)($stored['orderId'] ?? 0);
        $vars['smimeItemId']        = (int)($stored['itemId'] ?? 0);
        $vars['smimeVendorOrderId'] = (string)($stored['vendorOrderId'] ?? '');
        $vars['smimeValidFrom']     = $this->smimeDate($stored['validFrom'] ?? '');
        $vars['smimeValidTill']     = $this->smimeDate($stored['validTill'] ?? '');
        $vars['smimeOrderedAt']     = $this->smimeDate($stored['orderedAt'] ?? '');
        $vars['smimeCsrProvided']   = !empty($stored['csrProvided']);
        $vars                       = $this->gatewayRenewalVars(SmimeService::class, $serviceId, $stored, $input, $vars);
        // migrated TheSSLStore invite order: the client enrols on the certificate authority's page
        $vars['smimeEnrollmentLink'] = !$emails && !empty($stored['enrollmentLink']) && $vars['smimeTone'] === 'wait' ? (string)$stored['enrollmentLink'] : '';

        return ['tpl' => 'home_smime', 'vars' => $vars];
    }

    private function smimeConfigurationHTML($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $client    = Capsule::table('tblclients')->where('id', (int)$input['params']['userid'])->first();

        $phone = $client ? trim((string)$client->phonenumber) : '';
        if($phone !== '' && strpos($phone, '+') !== 0 && !empty($client->phonecc))
        {
            $phone = '+' . ltrim((string)$client->phonecc, '+') . '.' . $phone;
        }

        $previous = (array)(SmimeService::stored($serviceId)['previous'] ?? []);
        $last     = $previous ? (array)end($previous) : [];
        $lastTill = !empty($last['validTill']) ? strtotime($last['validTill']) : false;

        $vars['smimeRequiresOrganization'] = SmimeService::requiresOrganization((int)$input['params']['pid']);
        $vars['smimeRegionRequired']       = in_array('region', SmimeService::organizationRequired((int)$input['params']['pid']), true);
        $vars['smimeMaxEmails']            = SmimeService::MAX_EMAILS;
        $vars['smimeRenewal']              = (bool)$previous;
        // renewal: the certificate ordered before stays valid until its end date
        $vars['smimeCurrentValidTill']     = $lastTill && $lastTill > time() && ($last['status'] ?? '') === 'active' ? date('F j, Y', $lastTill) : '';
        $vars['smimePreviousEmails']       = array_values((array)($last['emails'] ?? []));
        $vars['agreement']                 = CertificateService::agreement((int)$input['params']['pid']);
        $vars['smimeClient']               = [
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

        return ['tpl' => 'home_smime_configuration', 'vars' => $vars];
    }

    public function createSmimeJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!SmimeService::isSmimeWhmcsProduct((int)$input['params']['pid']))
        {
            throw new \Exception(Lang::absoluteT('smimeNotSmimeProduct'));
        }
        if(($input['params']['status'] ?? '') !== 'Active')
        {
            throw new \Exception(Lang::absoluteT('smimeServiceNotActive'));
        }

        $emails = SmimeService::parseEmails([(string)($input['smime_email'] ?? ''), (string)($input['smime_additional_emails'] ?? '')], $invalid);
        if($invalid)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($invalid[0])));
        }
        if(!$emails)
        {
            throw new \Exception(Lang::absoluteT('smimeEmailRequired'));
        }
        if(count($emails) > SmimeService::MAX_EMAILS)
        {
            throw new \Exception(sprintf(Lang::absoluteT('smimeTooManyEmails'), SmimeService::MAX_EMAILS));
        }

        if(CertificateService::agreement((int)$input['params']['pid']) && empty($input['agreement']))
        {
            throw new \Exception(Lang::absoluteT('serverCA', 'home', 'sslConfigAgreementRequired'));
        }

        $csr = trim((string)($input['smime_csr'] ?? ''));
        if($csr !== '' && !@openssl_csr_get_subject($csr))
        {
            throw new \Exception(Lang::absoluteT('smimeInvalidCsr'));
        }

        $order = ['emails' => $emails, 'csr' => $csr];
        if(SmimeService::requiresOrganization((int)$input['params']['pid']))
        {
            $contacts = SmimeService::contacts((array)($input['org'] ?? []), (array)($input['admin'] ?? []), $missing, SmimeService::organizationRequired((int)$input['params']['pid']));
            if($missing)
            {
                throw new \Exception(sprintf(Lang::absoluteT('smimeFieldRequired'), Lang::absoluteT('serverCA', 'home', ($missing[0] === 'org' ? 'smimeOrg_' : 'smimeAdmin_') . $missing[1])));
            }
            if(filter_var($contacts['administrator']['email'], FILTER_VALIDATE_EMAIL) === false)
            {
                throw new \Exception(sprintf(Lang::absoluteT('smimeInvalidEmail'), htmlspecialchars($contacts['administrator']['email'])));
            }
            $order += $contacts;
        }

        // a double submit must not place two orders
        if(!$this->acquireServiceLock($serviceId))
        {
            throw new \Exception(Lang::absoluteT('smimeAlreadyOrdered'));
        }
        try
        {
            SmimeService::create($serviceId, $order);
        }
        finally
        {
            $this->releaseServiceLock($serviceId);
        }

        return ['success' => true, 'message' => Lang::absoluteT('smimeOrderedSuccessfully')];
    }

    public function refreshSmimeDetailsJSON($input, $vars = array())
    {
        $this->requireCsrfToken();
        $serviceId = (int)$input['params']['serviceid'];
        if(!SmimeService::isOrdered($serviceId))
        {
            throw new \Exception(Lang::absoluteT('smimeNotOrderedYet'));
        }
        SmimeService::refresh($serviceId);

        return ['success' => true, 'message' => Lang::absoluteT('smimeDetailsRefreshed')];
    }

    private function smimeBaseVars($input, $vars)
    {
        $serviceId = (int)$input['params']['serviceid'];
        $service   = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        $product   = Product::find((int)$input['params']['pid']);
        $currency  = $service ? \getCurrency((int)$service->userid) : null;

        $vars['allOk']       = true;
        $vars['serviceid']   = $serviceId;
        $vars['assetsURL']   = Server::I()->getAssetsURL();
        $vars['csrfToken']   = \generate_token('plain');
        $vars['productName'] = $product ? $product->name : '';
        $vars['billing']     = $service ? [
            'registration' => $this->smimeDate($service->regdate),
            'nextDue'      => $this->smimeDate($service->nextduedate),
            'amount'       => \formatCurrency($service->amount, $currency ? $currency['id'] : null),
            'cycle'        => $service->billingcycle,
        ] : [];

        return $vars;
    }

    private function smimeStatusText($status)
    {
        $text = Lang::absoluteT('serverCA', 'home', 'smimeStatus_' . $status);

        return (!is_string($text) || $text === '' || strpos($text, 'smimeStatus_') !== false) ? ucfirst($status) : $text;
    }

    /** 'November 6, 2026' (the client area date format of the module) */
    private function smimeDate($value)
    {
        $time = $value && strpos((string)$value, '0000-00-00') !== 0 ? strtotime((string)$value) : false;

        return $time ? date('F j, Y', $time) : '';
    }
}
