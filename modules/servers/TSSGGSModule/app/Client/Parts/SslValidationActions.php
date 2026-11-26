<?php

namespace TSSGGSModule\Server\app\Client\Parts;

use TSSGGSModule\Server\app\models\whmcs\Order;
use TSSGGSModule\Server\app\models\whmcs\Product;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\app\repository\whmcs\Domain;
use TSSGGSModule\Server\Text;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Libs\Certificates\CertificateSyncHelpers;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Support\ClientText;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;

/**
 * Domain validation actions of a pending SSL order: recheck, resend the approval e-mail, change the method.
 * Part of SslCertificatePages (used there as a trait).
 */
trait SslValidationActions
{
    public function revalidateHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();

            $ssl     = SSL::getByServiceId($serviceId);
            $service = Service::where('id', $serviceId)->first();
            if(!$ssl || !$service || $ssl->status !== 'Configuration Submitted' || empty($ssl->remoteid) || !CertificateService::serviceOpen($serviceId))
            {
                throw new \Exception(Text::get('revalidate_not_available'));
            }

            $this->getSslApiForProduct($service->packageid)->recheckValidation($ssl->remoteid);

            // Same flow as the SSLCertificates cron - fetch the order details right away
            $status      = '';
            $certificate = CertificateSyncHelpers::getCertificatesQuery()->where('tblhosting.id', $serviceId)->first();
            if($certificate)
            {
                $status = CertificateSyncHelpers::syncCertificate($certificate->toArray(), (new AddonModuleRepository())->getModuleConfiguration());
            }

            $_SESSION['mod-success'] = Text::get($status === 'active' ? 'revalidate_success_active' : 'revalidate_success_pending');
        }
        catch(\Exception $e)
        {
            $_SESSION['mod-error'] = Text::get('revalidate_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function resendValidationEmailHTML($input, $vars = [])
    {
        $serviceId = (int)$input['id'];

        try
        {
            $this->requireCsrfToken();

            $ssl     = SSL::getByServiceId($serviceId);
            $service = Service::where('id', $serviceId)->first();
            if(!$ssl || !$service || $ssl->status !== 'Configuration Submitted' || empty($ssl->remoteid) || !CertificateService::serviceOpen($serviceId))
            {
                throw new \Exception(Text::get('resend_dcv_not_available'));
            }

            $this->getSslApiForProduct($service->packageid)->resendValidationEmail($ssl->remoteid);

            $_SESSION['mod-success'] = Text::get('resend_dcv_success');
        }
        catch(\Exception $e)
        {
            $_SESSION['mod-error'] = Text::get('resend_dcv_error') . ' ' . $e->getMessage();
        }

        redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
    }

    public function changeApprovalMethodHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        if(!CertificateService::serviceOpen((int)$serviceId))
        {
            return ['tpl' => 'error', 'vars' => ['error' => ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.')]];
        }
        $certificate = Request::getOrder($serviceId);
        $service     = Service::where('id', $serviceId)->first();

        $configProduct = (new ProductRepository())->getProductConfiguration($service->packageid);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

        $available_method_validation = explode(',', $configProduct['dcv']);
        if(isset($addonConfig['sslSettings']['disableEmailValidation']) && !empty($addonConfig['sslSettings']['disableEmailValidation']))
        {
            $key = array_search('email', $available_method_validation);
            if($key !== false) unset($available_method_validation[$key]);

            $temp_available_method_validation = [];
            foreach($available_method_validation as $method)
            {
                $temp_available_method_validation[] = $method;
            }
            $available_method_validation = $temp_available_method_validation;
        }
        $vars['available_method_validation'] = $available_method_validation;

        $api = SSLTrustCenterApi::forVendor($configProduct['provider'], $configProduct['category']);

        if($_POST)
        {
            $domainChanges = [];
            $newOrderData  = $certificate['orderData']['orderData'];
            $orderDomains  = array_merge([(string)($newOrderData['common_name']['name'] ?? '')], array_column((array)($newOrderData['alternative_names'] ?? []), 'name'));
            $emailDomains  = [];
            try
            {
                $this->requireCsrfToken();
                foreach((array)($_POST['method'] ?? []) as $domainName => $methodDomain)
                {
                    // only the domains of this order and the methods the product offers
                    if(!in_array((string)$domainName, $orderDomains, true) || !in_array((string)$methodDomain, $available_method_validation, true))
                    {
                        throw new \Exception(Text::get('invalidValidationMethod'));
                    }
                    if($methodDomain == 'email')
                    {
                        $emailDomains[] = (string)$domainName;
                    }
                }
                // an approver address must be one the certificate authority offers for that domain
                $offered = [];
                foreach($emailDomains ? (array)$api->getDomainEmails($emailDomains) : [] as $row)
                {
                    $offered[(string)($row['domain'] ?? '')] = array_map('strtolower', (array)($row['emails'] ?? []));
                }
                foreach($emailDomains as $domainName)
                {
                    if(!in_array(strtolower((string)($_POST['approver'][$domainName] ?? '')), $offered[$domainName] ?? [], true))
                    {
                        throw new \Exception(Text::get('invalidValidationMethod'));
                    }
                }
            }
            catch(\Exception $exception)
            {
                $_SESSION['mod-error'] = $exception->getMessage();
                redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
            }
            foreach((array)($_POST['method'] ?? []) as $domainName => $methodDomain)
            {
                $methodValidaton = $methodDomain == 'email' ? $_POST['approver'][$domainName] : $methodDomain;
                $domainChanges[] = ['domain' => ['name' => $domainName, 'validation_method' => $methodValidaton]];

                if($domainName == $newOrderData['common_name']['name'])
                {
                    $newOrderData['common_name']['validation_method'] = $methodValidaton;
                }

                foreach((array)($newOrderData['alternative_names'] ?? []) as $key => $san)
                {
                    if($san['name'] == $domainName)
                    {
                        $newOrderData['alternative_names'][$key]['validation_method'] = $methodValidaton;
                    }
                }
            }
            try
            {

                $itemId = (int)SSL::getByServiceId($serviceId)->remoteid;
                foreach($domainChanges as $domainChange)
                {
                    $api->changeApprovers($itemId, $domainChange);
                }
                // the change went through; if the gateway can not show the order yet, the stored status stays until the sync reads it
                $newData                               = $api->getOrderOr($itemId, $serviceId, ['order' => $certificate['orderData']['order'] ?? [], 'items' => $certificate['orderData']['items'] ?? []]);
                $certificate['orderData']['orderData'] = $newOrderData;
                $certificate['orderData']['order']     = $newData['order'];
                $certificate['orderData']['items']     = $newData['items'];
                Request::updateOrderData($serviceId, $certificate);
                CertificateService::event($serviceId, 'info', '[Validation] Validation method changed by the client');
                $_SESSION['mod-success'] = Text::get('validation_method_changed');

            }
            catch(\Exception $exception)
            {
                $_SESSION['mod-error'] = $exception->getMessage();
            }

            redir('action=productdetails&id=' . $serviceId, 'clientarea.php');
        }

        $domains   = [];
        $domains[] = $certificate['orderData']['orderData']['common_name']['name'];
        foreach((array)($certificate['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $domains[] = $san['name'];
        }
        $vars['domains']   = $domains;

        // each domain starts on the method it uses now - as the gateway reports it (the enrollment data goes stale
        // after a change); an address is e-mail, dns-cname / dns-txt stay as they are when the product offers them
        $current = [$certificate['orderData']['orderData']['common_name']['name'] => $certificate['orderData']['orderData']['common_name']['validation_method'] ?? ''];
        foreach((array)($certificate['orderData']['orderData']['alternative_names'] ?? []) as $san)
        {
            $current[$san['name']] = $san['validation_method'] ?? '';
        }
        $gatewayItems = isset($certificate['orderData']['items']) && is_array($certificate['orderData']['items']) ? $certificate['orderData']['items'] : [];
        foreach((array)(reset($gatewayItems)['domains'] ?? []) as $gatewayDomain)
        {
            if(isset($current[$gatewayDomain['name'] ?? '']) && !empty($gatewayDomain['method']))
            {
                $current[$gatewayDomain['name']] = $gatewayDomain['method'];
            }
        }
        $vars['currentMethods'] = array_map(function($method) use ($available_method_validation) {
            $method = strtolower((string)$method);
            if(strpos($method, '@') !== false)
            {
                return 'email';
            }

            return strpos($method, 'dns') === 0 && !in_array($method, $available_method_validation, true) ? 'dns' : $method;
        }, $current);

        // IP addresses have no approver mailboxes (IP certificates validate by file only)
        $approvers         = \TSSGGSModule\App\Libs\Certificates\IpCertificate::isProduct((int)\WHMCS\Database\Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid'))
            ? array_map(function($name) { return ['domain' => $name, 'emails' => []]; }, $domains)
            : $api->getDomainEmails($domains);
        $vars['approvers'] = $this->approversToOptions($approvers);

        return ['tpl' => 'change_approval_method', 'vars' => $vars];
    }
}
