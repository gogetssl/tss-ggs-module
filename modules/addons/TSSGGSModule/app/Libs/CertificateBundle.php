<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Certificate download formats built from the files the gateway returns (certificate + intermediates).
 * Nothing here calls the gateway; PKCS#7 is assembled as a certificates-only SignedData structure.
 */
class CertificateBundle
{
    const FORMATS = ['crt', 'ca', 'fullchain', 'nginx', 'apache', 'pkcs7', 'zip'];

    protected $crt;
    protected $ca;
    protected $name;

    public function __construct($crt, $ca, $commonName)
    {
        $this->crt  = trim((string)$crt) . "\n";
        $this->ca   = trim((string)$ca) !== '' ? trim((string)$ca) . "\n" : '';
        $this->name = self::fileName($commonName);
    }

    public static function fromOrder(array $certificate)
    {
        $commonName = isset($certificate['orderData']['orderData']['common_name']['name']) ? $certificate['orderData']['orderData']['common_name']['name'] : '';
        $files      = AutoInstallHelpers::extractCertificate(isset($certificate['orderFiles']) ? $certificate['orderFiles'] : [], $commonName);

        if(trim($files['crt']) === '')
        {
            throw new \Exception('The certificate has not been issued yet');
        }

        return new self($files['crt'], $files['ca'], $commonName);
    }

    public static function fileName($commonName)
    {
        $name = str_replace('*', 'star', strtolower(trim((string)$commonName)));
        $name = preg_replace('/[^a-z0-9.-]+/', '_', $name);

        return $name !== '' ? str_replace('.', '_', $name) : 'certificate';
    }

    public function crt()
    {
        return $this->crt;
    }

    public function ca()
    {
        return $this->ca;
    }

    /**
     * Download for a format: ['filename' => .., 'mime' => .., 'content' => ..]
     */
    public function get($format)
    {
        switch($format)
        {
            case 'crt':
                return $this->file($this->name . '.crt', $this->crt);
            case 'ca':
                return $this->file($this->name . '.ca-bundle', $this->ca);
            case 'fullchain':
            case 'nginx':
                // Nginx expects the certificate followed by the intermediates in one file
                return $this->file($this->name . ($format === 'nginx' ? '.chained.crt' : '.fullchain.crt'), $this->crt . $this->ca);
            case 'apache':
                return $this->zip($this->name . '_apache.zip', [
                    $this->name . '.crt'       => $this->crt,
                    $this->name . '.ca-bundle' => $this->ca,
                ]);
            case 'pkcs7':
                return $this->file($this->name . '.p7b', $this->pkcs7(), 'application/x-pkcs7-certificates');
            case 'zip':
                return $this->zip($this->name . '.zip', [
                    $this->name . '.crt'           => $this->crt,
                    $this->name . '.ca-bundle'     => $this->ca,
                    $this->name . '.chained.crt'   => $this->crt . $this->ca,
                    $this->name . '.p7b'           => $this->pkcs7(),
                ]);
        }

        throw new \Exception('Unknown certificate format');
    }

    /**
     * PEM encoded PKCS#7 (certificates only, no signers) - the .p7b format used by IIS, Tomcat and Java keytool
     */
    public function pkcs7()
    {
        $certs = '';
        foreach(self::splitPem($this->crt . $this->ca) as $pem)
        {
            $certs .= base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem));
        }

        $signedData = self::der(0x30,
            self::der(0x02, "\x01")                                   // version
            . self::der(0x31, '')                                     // digestAlgorithms
            . self::der(0x30, self::der(0x06, "\x2a\x86\x48\x86\xf7\x0d\x01\x07\x01")) // contentInfo: data
            . self::der(0xa0, $certs)                                 // [0] certificates
            . self::der(0x31, '')                                     // signerInfos
        );
        $contentInfo = self::der(0x30,
            self::der(0x06, "\x2a\x86\x48\x86\xf7\x0d\x01\x07\x02")    // signedData
            . self::der(0xa0, $signedData)
        );

        return "-----BEGIN PKCS7-----\n" . chunk_split(base64_encode($contentInfo), 64, "\n") . "-----END PKCS7-----\n";
    }

    public static function splitPem($pem)
    {
        preg_match_all('/-----BEGIN CERTIFICATE-----.+?-----END CERTIFICATE-----/s', (string)$pem, $matches);

        return $matches[0];
    }

    protected static function der($tag, $value)
    {
        $length = strlen($value);
        if($length < 0x80)
        {
            return chr($tag) . chr($length) . $value;
        }

        $bytes = ltrim(pack('N', $length), "\0");

        return chr($tag) . chr(0x80 | strlen($bytes)) . $bytes . $value;
    }

    protected function file($filename, $content, $mime = 'application/x-x509-ca-cert')
    {
        return ['filename' => $filename, 'mime' => $mime, 'content' => $content];
    }

    protected function zip($filename, array $files)
    {
        if(!class_exists('\ZipArchive'))
        {
            throw new \Exception('The PHP zip extension is not available on this server');
        }

        $path = tempnam(sys_get_temp_dir(), 'tssggs');
        $zip  = new \ZipArchive();
        if($zip->open($path, \ZipArchive::OVERWRITE) !== true)
        {
            throw new \Exception('Unable to create the ZIP file');
        }
        foreach($files as $name => $content)
        {
            if(trim($content) !== '')
            {
                $zip->addFromString($name, $content);
            }
        }
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        return ['filename' => $filename, 'mime' => 'application/zip', 'content' => $content];
    }

    /**
     * Sends the download to the browser and stops
     */
    public static function output(array $download)
    {
        while(ob_get_level() > 0)
        {
            ob_end_clean();
        }
        header('Content-Type: ' . $download['mime']);
        header('Content-Disposition: attachment; filename="' . $download['filename'] . '"');
        header('Content-Length: ' . strlen($download['content']));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, must-revalidate');
        header('Expires: 0');
        echo $download['content'];
        exit;
    }
}
