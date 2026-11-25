<?php

namespace TSSGGSModule\Server\app\controllers\server\admin;


use TSSGGSModule\Server\app\services\AdminServicesTabFields;
use TSSGGSModule\Server\app\services\CreateAccount;
use TSSGGSModule\Server\app\services\Renew;
use TSSGGSModule\Server\app\services\SSLStepOne;
use TSSGGSModule\Server\app\services\SSLStepTwo;
use TSSGGSModule\Server\app\services\SSLStepThree;
use TSSGGSModule\Server\app\services\SyncCertificate;
use TSSGGSModule\App\Libs\Support\AdminText;
use TSSGGSModule\App\Libs\Certificates\CertificateBundle;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Support\ClientText;
use TSSGGSModule\App\Libs\Support\GeneralSettings;

class Actions {

    public function ConfigOptions(){

        if($_POST['action'] == 'save')
        {
            return [];
        }

        $data['mode'] = "advanced";
        $data['content'] = '<div class="errorbox"><strong><span class="title">' . AdminText::html('module_information', 'Module Information') . '</span></strong><br>' . AdminText::html('module_managed_in_addon', 'This module can only be managed from the :addon: addon module.', ['addon' => 'TSSGGSModule']) . '</div>';
        ob_clean();
        header('Content-Type: application/json');

        echo json_encode($data);
        die();
    }

    public function CreateAccount($params)
    {
        try {

            return (new CreateAccount())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    /**
     * Only CodeGuard can be suspended at the vendor (the licence stops backing up, the data is kept); a certificate
     * has nothing to suspend, so the service is only marked suspended in WHMCS.
     */
    public function SuspendAccount($params)
    {
        return $this->codeGuardState($params, true);
    }

    public function UnsuspendAccount($params)
    {
        return $this->codeGuardState($params, false);
    }

    private function codeGuardState($params, $suspend)
    {
        $serviceId = (int)$params['serviceid'];
        if(!\TSSGGSModule\App\Libs\ProductLines\CodeGuardService::isCodeGuardService($serviceId))
        {
            return 'success';
        }

        return $this->run(function() use ($serviceId, $suspend) {
            $suspend
                ? \TSSGGSModule\App\Libs\ProductLines\CodeGuardService::suspend($serviceId)
                : \TSSGGSModule\App\Libs\ProductLines\CodeGuardService::activate($serviceId);
        });
    }

    /**
     * Product change: CodeGuard moves the licence to the other plan at the vendor. The other product types have no
     * plan change at the vendor - another certificate product means a new order - so for them there is nothing to do
     * here. They must still answer "success": WHMCS takes any other answer as a module failure, and before this
     * function existed their product change went through untouched.
     */
    public function ChangePackage($params)
    {
        $serviceId = (int)$params['serviceid'];
        if(!\TSSGGSModule\App\Libs\ProductLines\CodeGuardService::isCodeGuardService($serviceId))
        {
            return 'success';
        }

        return $this->run(function() use ($serviceId, $params) {
            \TSSGGSModule\App\Libs\ProductLines\CodeGuardService::changePlan($serviceId, (int)$params['pid']);
        });
    }

    /**
     * Cancels the vendor order / subscription so it does not stay billed at the gateway (can be turned off in the settings)
     */
    public function TerminateAccount($params)
    {
        // the private keys go in any case, also when the vendor order is left open
        CertificateService::forgetPrivateKeys($params['serviceid']);
        // a certificate bought at payment whose details were never submitted is always cancelled: nothing was issued
        if(!GeneralSettings::enabled('terminateCancelsOrder') && !\TSSGGSModule\App\Libs\Certificates\IncompleteOrder::pending($params['serviceid']))
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
                throw new \Exception(AdminText::get('error_no_open_order', 'There is no open vendor order to cancel'));
            }
            // the certificate no longer exists - stop renewal invoices and client actions (same as the client's cancel)
            \WHMCS\Database\Capsule::table('tblhosting')->where('id', (int)$params['serviceid'])
                ->whereIn('domainstatus', ['Pending', 'Active', 'Suspended'])->update(['domainstatus' => 'Cancelled']);
            CertificateService::forgetPrivateKeys($params['serviceid']);
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
            throw new \Exception(ClientText::get('noFileValidation', 'The order does not use file (HTTP/HTTPS) validation'));
        });
    }

    /**
     * Client "Reissue Certificate" button - the reissue form of the module
     */
    public function ReissueCertificate($params)
    {
        if(!CertificateService::serviceOpen($params['serviceid']))
        {
            return ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.');
        }
        if(CertificateService::status($params['serviceid']) !== 'active')
        {
            return ClientText::get('reissueAfterIssue', 'The certificate can be reissued once it has been issued');
        }

        header('Location: clientarea.php?action=productdetails&id=' . (int)$params['serviceid'] . '&mod-action=reissueSSL');
        exit;
    }

    /** Client "Download Certificate" button (the admin uses GetCertificate) */
    public function DownloadCertificate($params)
    {
        if(!CertificateService::usable($params['serviceid']))
        {
            return ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.');
        }

        return $this->GetCertificate($params);
    }

    /**
     * Client "Contact Details" - contacts sent to the CA with the order
     */
    public function ContactDetails($params)
    {
        $labels = [
            'administrator' => ClientText::get('contactAdministrator', 'Administrative contact'),
            'technical'     => ClientText::get('contactTechnical', 'Technical contact'),
            'organization'  => ClientText::get('contactOrganization', 'Organization'),
        ];

        // field names of the order's contacts (SSLStepThree) => client language
        $fieldLabels = [
            'first_name'     => ClientText::get('contactFirstName', 'First name'),
            'last_name'      => ClientText::get('contactLastName', 'Last name'),
            'title'          => ClientText::get('contactJobTitle', 'Job title'),
            'email'          => ClientText::get('contactEmail', 'Email'),
            'phone'          => ClientText::get('contactPhone', 'Phone'),
            'name'           => ClientText::get('contactName', 'Name'),
            'address_line_1' => ClientText::get('contactAddress1', 'Address line 1'),
            'address_line_2' => ClientText::get('contactAddress2', 'Address line 2'),
            'city'           => ClientText::get('contactCity', 'City'),
            'region'         => ClientText::get('contactRegion', 'State / region'),
            'country'        => ClientText::get('contactCountry', 'Country'),
            'postal_code'    => ClientText::get('contactPostalCode', 'Postal code'),
        ];

        $contacts = [];
        foreach(CertificateService::contacts($params['serviceid']) as $type => $contact)
        {
            $fields = [];
            foreach((array)$contact as $field => $value)
            {
                if(is_scalar($value) && trim((string)$value) !== '')
                {
                    $fields[isset($fieldLabels[$field]) ? $fieldLabels[$field] : ucfirst(str_replace('_', ' ', (string)$field))] = $value;
                }
            }
            $contacts[] = ['title' => isset($labels[$type]) ? $labels[$type] : ucfirst($type), 'fields' => $fields];
        }

        return [
            'templatefile' => 'templates/clientarea/default/pages/home/contact_details',
            'vars'         => ['contacts' => $contacts, 'serviceid' => (int)$params['serviceid']],
        ];
    }

    /**
     * Admin download as a normal page request (the AdminAreaPage hook in hooks.php calls it): WHMCS runs module
     * buttons through AJAX, where a file never reaches the browser. $what: certificate (ZIP) | authfile
     */
    public static function adminDownload($serviceId, $what)
    {
        try
        {
            if($what === 'authfile')
            {
                $stored = CertificateService::stored($serviceId);
                $items  = isset($stored['orderData']['items']) ? (array)$stored['orderData']['items'] : [];
                $item   = (array)reset($items);
                foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
                {
                    if(in_array($domain['method'] ?? '', ['http', 'https'], true) && !empty($domain['validation']['file_name']))
                    {
                        CertificateBundle::output(['filename' => basename($domain['validation']['file_name']), 'mime' => 'text/plain', 'content' => (string)$domain['validation']['value']]);
                    }
                }
                throw new \Exception(AdminText::get('error_no_file_validation', 'The order does not use file (HTTP/HTTPS) validation'));
            }

            CertificateBundle::output(CertificateService::bundle($serviceId)->get('zip'));
        }
        catch(\Throwable $e)
        {
            header('Content-Type: text/plain; charset=utf-8', true, 404);
            echo $e->getMessage();
            exit;
        }
    }

    /** "Resend Approver Email" only makes sense while a domain waits for an e-mail approval */
    private static function usesEmailValidation($serviceId)
    {
        $stored = CertificateService::stored($serviceId);
        $items  = isset($stored['orderData']['items']) ? (array)$stored['orderData']['items'] : [];
        $item   = (array)reset($items);
        foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
        {
            $method = strtolower((string)($domain['method'] ?? ''));
            if(empty($domain['approved']) && ($method === 'email' || strpos($method, '@') !== false))
            {
                return true;
            }
        }

        return false;
    }

    public function ClientAreaCustomButtonArray($params = [])
    {
        $serviceId = isset($params['serviceid']) ? (int)$params['serviceid'] : 0;
        if(!$serviceId || CertificateService::category($serviceId) !== CertificateService::CATEGORY_TLS)
        {
            return [];
        }

        $buttons = [];
        // Reissue and Download are already in the sidebar (hooks.php) - no second entry under another name
        if(CertificateService::contacts($serviceId))
        {
            $buttons[ClientText::get('contactDetails', 'Contact Details')] = 'ClientAreaCustomContactDetails';
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

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepOne($params)
    {
        try {

            return (new SSLStepOne())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepTwo($params)
    {
        try {

            return (new SSLStepTwo())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepThree($params)
    {
        try {

            $params = array_merge($_POST, $params);
            return (new SSLStepThree())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    public function AdminCustomButtonArray($params = [])
    {
        $serviceId = isset($params['serviceid']) ? (int)$params['serviceid'] : 0;
        $gatewayType = $serviceId ? \TSSGGSModule\App\Libs\ProductLines\GatewayOrderService::typeOfService($serviceId) : null;
        if($gatewayType)
        {
            // nothing to refresh or cancel before the client orders the certificate
            return $gatewayType::isOrdered($serviceId)
                ? [AdminText::get('button_refresh_status', 'Refresh :product: Status', ['product' => $gatewayType::LABEL]) => 'SyncCertificate', AdminText::get('button_cancel_order', 'Cancel Order') => 'CancelOrder']
                : [];
        }
        if($serviceId && CertificateService::category($serviceId) !== CertificateService::CATEGORY_TLS)
        {
            return [AdminText::get('button_cancel_order', 'Cancel Order') => 'CancelOrder'];
        }

        $status  = $serviceId ? CertificateService::status($serviceId) : '';
        $buttons = [AdminText::get('button_sync_certificate', 'Sync Certificate') => 'SyncCertificate'];

        if(!$serviceId || in_array($status, ['processing', 'pending'], true))
        {
            $buttons[AdminText::get('button_recheck_validation', 'Recheck Validation')] = 'RecheckValidation';
            if(!$serviceId || self::usesEmailValidation($serviceId))
            {
                $buttons[AdminText::get('button_resend_approver_email', 'Resend Approver Email')] = 'SSLAdminResendApproverEmail';
            }
            // the validation file and the certificate are download links in the module tab (AdminServicesTabFields)
        }
        if(!$serviceId || $status === 'active')
        {
            $buttons[AdminText::get('button_resend_certificate', 'Resend Certificate')] = 'SSLAdminResendCertificate';
        }
        if(!$serviceId || ($status !== '' && !in_array($status, ['cancelled', 'expired', 'rejected', 'revoked', 'refunded'], true)))
        {
            $buttons[AdminText::get('button_cancel_order', 'Cancel Order')] = 'CancelOrder';
        }

        return $buttons;
    }

    public function SyncCertificate($params)
    {
        try {

            return (new SyncCertificate())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

    public function AdminServicesTabFields($params)
    {
        try {

            return (new AdminServicesTabFields())->run($params);

        } catch(\Throwable $e) {

            return $e->getMessage();

        }
    }

}
