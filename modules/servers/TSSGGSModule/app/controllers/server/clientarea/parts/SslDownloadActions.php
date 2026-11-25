<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateBundle;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Downloads and sending of an SSL certificate: files, bundle, private key, CSR, validation file.
 * Part of SslCertificatePages (used there as a trait).
 */
trait SslDownloadActions
{
    public function sendCertificateHTML($input, $vars = [])
    {
        try
        {
            $this->requireCsrfToken();
            CertificateService::sendCertificate((int)$input['id']);
            redir('action=productdetails&id=' . (int)$input['id'] . '&sendEmail=true', 'clientarea.php');
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
    }

    /**
     * Certificate download in any format of CertificateBundle::FORMATS (crt, ca, fullchain, nginx, apache, pkcs7, zip)
     */
    public function downloadHTML($input, $vars = [])
    {
        $format = isset($input['format']) ? (string)$input['format'] : 'zip';

        try
        {
            if(!in_array($format, CertificateBundle::enabledFormats(), true))
            {
                throw new \Exception(Lang::absoluteT('download_unknown_format'));
            }
            // the issued certificate, or during a renewal the previous one that is still valid
            CertificateBundle::output(CertificateService::downloadableBundle((int)$input['id'])->get($format));
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
    }

    public function downloadCRTHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'crt']), $vars);
    }

    public function downloadICRTHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'ca']), $vars);
    }

    public function downloadSSLHTML($input, $vars = [])
    {
        return $this->downloadHTML(array_merge($input, ['format' => 'fullchain']), $vars);
    }

    public function downloadKeyHTML($input, $vars = [])
    {
        // POST + token, and only a key this client's session generated for this service (no URL, no decrypt oracle)
        try
        {
            $this->requireCsrfToken();
            $decodeKey = \ModulesGarden\TSSGGSModule\App\Libs\Certificates\PrivateKeyDownload::fetch((int)$input['id'], (string)($_POST['handle'] ?? ''));
            if($decodeKey === null)
            {
                throw new \Exception(Lang::absoluteT('private_key_expired'));
            }
        }
        catch(\Exception $e)
        {
            $_SESSION['mg-error'] = $e->getMessage();
            redir('action=productdetails&id=' . (int)$input['id'], 'clientarea.php');
        }
        $keyDomain = (string)Service::where('id', (int)$input['id'])->value('domain');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . CertificateBundle::fileName($keyDomain !== '' ? $keyDomain : 'private') . '.key"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $decodeKey;
        exit;

    }

    public function downloadCSRHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        $certificate = Request::getOrder($serviceId);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $certificate['orderData']['orderData']['common_name']['name'] . '.csr');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $certificate['orderData']['orderData']['csr'];
        exit;
    }

    public function downloadAuthFileHTML($input, $vars = [])
    {
        $serviceId   = $input['id'];
        $certificate = Request::getOrder($serviceId);

        $mainDomainValidation = $certificate['orderData']['orderData']['common_name']['name'];

        $items = reset($certificate['orderData']['items']);

        $fileName    = '';
        $fileContent = '';

        foreach($items['domains'] as $domain)
        {
            if($domain['name'] == $mainDomainValidation && ($domain['method'] == 'http' || $domain['method'] == 'https'))
            {
                $fileName    = $domain['validation']['file_name'];
                $fileContent = $domain['validation']['value'];
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        print $fileContent;
        exit;
    }
}
