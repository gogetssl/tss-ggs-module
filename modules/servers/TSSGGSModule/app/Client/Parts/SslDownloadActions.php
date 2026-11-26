<?php

namespace TSSGGSModule\Server\app\Client\Parts;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\Text;
use TSSGGSModule\App\Libs\Certificates\CertificateBundle;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Models\Request;

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
            $_SESSION['mod-error'] = $e->getMessage();
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
                throw new \Exception(Text::get('download_unknown_format'));
            }
            // the issued certificate, or during a renewal the previous one that is still valid
            CertificateBundle::output(CertificateService::downloadableBundle((int)$input['id'])->get($format));
        }
        catch(\Exception $e)
        {
            $_SESSION['mod-error'] = $e->getMessage();
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
            $decodeKey = \TSSGGSModule\App\Libs\Certificates\PrivateKeyDownload::fetch((int)$input['id'], (string)($_POST['handle'] ?? ''));
            if($decodeKey === null)
            {
                throw new \Exception(Text::get('private_key_expired'));
            }
        }
        catch(\Exception $e)
        {
            $_SESSION['mod-error'] = $e->getMessage();
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
