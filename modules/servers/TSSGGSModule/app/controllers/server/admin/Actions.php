<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\admin;


use ModulesGarden\Servers\TSSGGSModule\app\services\AdminServicesTabFields;
use ModulesGarden\Servers\TSSGGSModule\app\services\CreateAccount;
use ModulesGarden\Servers\TSSGGSModule\app\services\Renew;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepOne;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepTwo;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepThree;
use ModulesGarden\Servers\TSSGGSModule\app\services\SyncCertificate;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateBundle;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

class Actions {

    public function ConfigOptions(){

        if($_POST['action'] == 'save')
        {
            return [];
        }

        $data['mode'] = "advanced";
        $data['content'] = '<div class="errorbox"><strong><span class="title">Module Information</span></strong><br>This module can only be managed from the TSSGGSModule addon module.</div>';
        ob_clean();
        header('Content-Type: application/json');

        echo json_encode($data);
        die();
    }

    public function CreateAccount($params)
    {
        try {

            return (new CreateAccount())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SuspendAccount($params)
    {
        return 'success';
    }

    public function UnsuspendAccount($params)
    {
        return 'success';
    }

    /**
     * Cancels the vendor order / subscription so it does not stay billed at the gateway (can be turned off in the settings)
     */
    public function TerminateAccount($params)
    {
        if(!GeneralSettings::enabled('terminateCancelsOrder'))
        {
            return 'success';
        }

        return $this->run(function() use ($params) {
            CertificateService::cancel($params['serviceid'], 'Service terminated in WHMCS');
        });
    }

    public function ResendApproverEmail($params)
    {
        return $this->run(function() use ($params) {
            CertificateService::resendApproverEmail($params['serviceid']);
        });
    }

    public function ResendCertificate($params)
    {
        return $this->run(function() use ($params) {
            CertificateService::sendCertificate($params['serviceid']);
        });
    }

    public function RecheckValidation($params)
    {
        return $this->run(function() use ($params) {
            CertificateService::revalidate($params['serviceid']);
        });
    }

    public function CancelOrder($params)
    {
        return $this->run(function() use ($params) {
            if(!CertificateService::cancel($params['serviceid'], 'Cancelled by administrator'))
            {
                throw new \Exception('There is no open vendor order to cancel');
            }
        });
    }

    /**
     * Admin "Download Certificate" (ZIP with all formats) - also WHMCS's SSLAdminGetCertificate
     */
    public function GetCertificate($params)
    {
        return $this->run(function() use ($params) {
            CertificateBundle::output(CertificateService::bundle($params['serviceid'])->get('zip'));
        });
    }

    public function DownloadAuthFile($params)
    {
        return $this->run(function() use ($params) {
            $stored = CertificateService::stored($params['serviceid']);
            $items  = isset($stored['orderData']['items']) ? (array)$stored['orderData']['items'] : [];
            $item   = (array)reset($items);
            foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
            {
                if(in_array($domain['method'] ?? '', ['http', 'https'], true) && !empty($domain['validation']['file_name']))
                {
                    CertificateBundle::output(['filename' => basename($domain['validation']['file_name']), 'mime' => 'text/plain', 'content' => (string)$domain['validation']['value']]);
                }
            }
            throw new \Exception('The order does not use file (HTTP/HTTPS) validation');
        });
    }

    /**
     * Client "Reissue Certificate" button - the reissue form of the module
     */
    public function ReissueCertificate($params)
    {
        if(CertificateService::status($params['serviceid']) !== 'active')
        {
            return 'The certificate can be reissued once it has been issued';
        }

        header('Location: clientarea.php?action=productdetails&id=' . (int)$params['serviceid'] . '&mg-action=reissueSSL');
        exit;
    }

    public function DownloadCertificate($params)
    {
        return $this->GetCertificate($params);
    }

    /**
     * Client "Contact Details" - contacts sent to the CA with the order
     */
    public function ContactDetails($params)
    {
        $labels = [
            'administrator' => 'Administrative contact',
            'technical'     => 'Technical contact',
            'organization'  => 'Organization',
        ];

        $contacts = [];
        foreach(CertificateService::contacts($params['serviceid']) as $type => $contact)
        {
            $contacts[] = ['title' => isset($labels[$type]) ? $labels[$type] : ucfirst($type), 'fields' => array_filter((array)$contact, function($value) {
                return is_scalar($value) && trim((string)$value) !== '';
            })];
        }

        return [
            'templatefile' => 'templates/clientarea/default/pages/home/contact_details',
            'vars'         => ['contacts' => $contacts, 'serviceid' => (int)$params['serviceid']],
        ];
    }

    public function ClientAreaCustomButtonArray($params = [])
    {
        $serviceId = isset($params['serviceid']) ? (int)$params['serviceid'] : 0;
        if(!$serviceId || CertificateService::category($serviceId) !== CertificateService::CATEGORY_TLS)
        {
            return [];
        }

        $buttons = [];
        if(CertificateService::status($serviceId) === 'active')
        {
            $buttons['Reissue Certificate']  = 'ClientAreaCustomReissueCertificate';
            $buttons['Download Certificate'] = 'ClientAreaCustomDownloadCertificate';
        }
        if(CertificateService::contacts($serviceId))
        {
            $buttons['Contact Details'] = 'ClientAreaCustomContactDetails';
        }

        return $buttons;
    }

    public function ClientAreaAllowedFunctions()
    {
        return ['ClientAreaCustomReissueCertificate', 'ClientAreaCustomDownloadCertificate', 'ClientAreaCustomContactDetails'];
    }

    /**
     * Runs an action and returns what WHMCS expects from a module function: 'success' or the error message
     */
    protected function run(callable $action)
    {
        try
        {
            $action();

            return 'success';
        }
        catch(\Throwable $e)
        {
            return $e->getMessage();
        }
    }

    public function Renew($params)
    {
        try {

            return (new Renew())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepOne($params)
    {
        try {

            return (new SSLStepOne())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepTwo($params)
    {
        try {

            return (new SSLStepTwo())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepThree($params)
    {
        try {

            $params = array_merge($_POST, $params);
            return (new SSLStepThree())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function AdminCustomButtonArray($params = [])
    {
        $serviceId = isset($params['serviceid']) ? (int)$params['serviceid'] : 0;
        if($serviceId && CertificateService::category($serviceId) !== CertificateService::CATEGORY_TLS)
        {
            return ['Cancel Order' => 'CancelOrder'];
        }

        $status  = $serviceId ? CertificateService::status($serviceId) : '';
        $buttons = ['Sync Certificate' => 'SyncCertificate'];

        if(!$serviceId || in_array($status, ['processing', 'pending'], true))
        {
            $buttons['Recheck Validation']    = 'RecheckValidation';
            $buttons['Resend Approver Email'] = 'SSLAdminResendApproverEmail';
            $buttons['Download Auth File']    = 'DownloadAuthFile';
        }
        if(!$serviceId || $status === 'active')
        {
            $buttons['Download Certificate'] = 'SSLAdminGetCertificate';
            $buttons['Resend Certificate']   = 'SSLAdminResendCertificate';
        }
        if(!$serviceId || ($status !== '' && !in_array($status, ['cancelled', 'expired', 'rejected', 'revoked', 'refunded'], true)))
        {
            $buttons['Cancel Order'] = 'CancelOrder';
        }

        return $buttons;
    }

    public function SyncCertificate($params)
    {
        try {

            return (new SyncCertificate())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function AdminServicesTabFields($params)
    {
        try {

            return (new AdminServicesTabFields())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

}

