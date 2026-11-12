<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Stand-alone SSL tools for the client area: everything runs on this server with PHP's OpenSSL, no gateway call and
 * nothing is stored. CSR / certificate decoder, key match, format converter, CSR generator, remote SSL checker.
 */
class SslTools
{
    /** ports the SSL checker may connect to */
    const CHECK_PORTS = [443, 8443, 465, 993, 995];

    // ---- decoders ---- //

    /**
     * @throws \Exception when the text is not a CSR
     */
    public static function decodeCsr($pem)
    {
        $pem = self::pem($pem, 'CERTIFICATE REQUEST');
        $csr = @openssl_csr_get_subject($pem, false);
        if(!$csr)
        {
            throw new \Exception('This is not a valid CSR (PEM, -----BEGIN CERTIFICATE REQUEST-----).');
        }

        $key     = @openssl_csr_get_public_key($pem);
        $details = $key ? openssl_pkey_get_details($key) : [];

        return [
            'subject'   => self::flatten($csr),
            'sans'      => self::csrSans($pem),
            'keyType'   => self::keyType($details),
            'keyBits'   => isset($details['bits']) ? (int)$details['bits'] : null,
            'valid'     => $key ? true : false,
        ];
    }

    /**
     * @throws \Exception when the text is not a certificate
     */
    public static function decodeCertificate($pem)
    {
        $pem  = self::pem($pem, 'CERTIFICATE');
        $info = @openssl_x509_parse($pem);
        if(!$info)
        {
            throw new \Exception('This is not a valid certificate (PEM, -----BEGIN CERTIFICATE-----).');
        }

        $key     = @openssl_pkey_get_public($pem);
        $details = $key ? openssl_pkey_get_details($key) : [];
        $sans    = [];
        if(!empty($info['extensions']['subjectAltName']))
        {
            foreach(explode(',', $info['extensions']['subjectAltName']) as $san)
            {
                $san = trim($san);
                if(stripos($san, 'DNS:') === 0 || stripos($san, 'IP Address:') === 0)
                {
                    $sans[] = trim(substr($san, strpos($san, ':') + 1));
                }
            }
        }

        return [
            'subject'     => self::flatten($info['subject']),
            'issuer'      => self::flatten($info['issuer']),
            'sans'        => $sans,
            'validFrom'   => date('Y-m-d H:i:s', $info['validFrom_time_t']) . ' UTC',
            'validTo'     => date('Y-m-d H:i:s', $info['validTo_time_t']) . ' UTC',
            'daysLeft'    => (int)floor(($info['validTo_time_t'] - time()) / 86400),
            'serial'      => isset($info['serialNumberHex']) ? $info['serialNumberHex'] : $info['serialNumber'],
            'signature'   => isset($info['signatureTypeSN']) ? $info['signatureTypeSN'] : '',
            'keyType'     => self::keyType($details),
            'keyBits'     => isset($details['bits']) ? (int)$details['bits'] : null,
            'sha1'        => openssl_x509_fingerprint($pem, 'sha1'),
            'sha256'      => openssl_x509_fingerprint($pem, 'sha256'),
        ];
    }

    // ---- key match ---- //

    /**
     * Compares the public keys of a certificate, a CSR and a private key (any combination of two or three)
     * @return array ['items' => [label => fingerprint], 'match' => bool]
     * @throws \Exception
     */
    public static function matchKeys($certificate, $csr, $privateKey)
    {
        $items = [];
        if(trim((string)$certificate) !== '')
        {
            $key = @openssl_pkey_get_public(self::pem($certificate, 'CERTIFICATE'));
            if(!$key)
            {
                throw new \Exception('The certificate is not valid.');
            }
            $items['Certificate'] = self::fingerprint($key);
        }
        if(trim((string)$csr) !== '')
        {
            $key = @openssl_csr_get_public_key(self::pem($csr, 'CERTIFICATE REQUEST'));
            if(!$key)
            {
                throw new \Exception('The CSR is not valid.');
            }
            $items['CSR'] = self::fingerprint($key);
        }
        if(trim((string)$privateKey) !== '')
        {
            $key = @openssl_pkey_get_private(trim($privateKey));
            if(!$key)
            {
                throw new \Exception('The private key is not valid (encrypted keys are not supported).');
            }
            $items['Private key'] = self::fingerprint($key);
        }
        if(count($items) < 2)
        {
            throw new \Exception('Enter at least two of: certificate, CSR, private key.');
        }

        return ['items' => $items, 'match' => count(array_unique($items)) === 1];
    }

    // ---- converter ---- //

    /**
     * @param string $mode pem2der | der2pem | pem2pfx | pfx2pem
     * @return array ['filename', 'mime', 'content']
     * @throws \Exception
     */
    public static function convert($mode, $certificate, $privateKey, $password, $chain = '')
    {
        switch($mode)
        {
            case 'pem2der':
                $pem = self::pem($certificate, 'CERTIFICATE');
                if(!@openssl_x509_read($pem))
                {
                    throw new \Exception('The certificate is not valid.');
                }
                $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem));

                return ['filename' => 'certificate.der', 'mime' => 'application/x-x509-ca-cert', 'content' => $der];

            case 'der2pem':
                if($certificate === '' || !@openssl_x509_read(self::derToPem($certificate)))
                {
                    throw new \Exception('The file is not a DER certificate.');
                }

                return ['filename' => 'certificate.pem', 'mime' => 'application/x-pem-file', 'content' => self::derToPem($certificate)];

            case 'pem2pfx':
                if(trim((string)$privateKey) === '')
                {
                    throw new \Exception('A PFX needs the private key.');
                }
                $key = @openssl_pkey_get_private(trim($privateKey));
                if(!$key)
                {
                    throw new \Exception('The private key is not valid (encrypted keys are not supported).');
                }
                $cert = self::pem($certificate, 'CERTIFICATE');
                if(!@openssl_x509_check_private_key($cert, $key))
                {
                    throw new \Exception('The private key does not belong to the certificate.');
                }
                $options = [];
                if(trim((string)$chain) !== '')
                {
                    // a string would add the first certificate of the chain only
                    preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', (string)$chain, $blocks);
                    $options['extracerts'] = $blocks[0] ?: trim($chain);
                }
                if(!openssl_pkcs12_export($cert, $pfx, $key, (string)$password, $options))
                {
                    throw new \Exception('The PFX could not be created.');
                }

                return ['filename' => 'certificate.pfx', 'mime' => 'application/x-pkcs12', 'content' => $pfx];

            case 'pfx2pem':
                $certs = [];
                if($certificate === '' || !@openssl_pkcs12_read($certificate, $certs, (string)$password))
                {
                    throw new \Exception('The PFX could not be read - wrong password or not a PFX file.');
                }
                $pem = (isset($certs['cert']) ? $certs['cert'] : '') . (isset($certs['pkey']) ? $certs['pkey'] : '');
                foreach(isset($certs['extracerts']) ? (array)$certs['extracerts'] : [] as $extra)
                {
                    $pem .= $extra;
                }

                return ['filename' => 'certificate.pem', 'mime' => 'application/x-pem-file', 'content' => $pem];
        }

        throw new \Exception('Choose a conversion.');
    }

    // ---- CSR generator ---- //

    /**
     * New RSA key + CSR; nothing is stored on the server
     * @return array ['csr', 'key']
     * @throws \Exception
     */
    public static function generate(array $dn, $bits = 2048)
    {
        $bits = in_array((int)$bits, [2048, 3072, 4096], true) ? (int)$bits : 2048;
        $name = array_filter([
            'countryName'            => strtoupper(substr(trim((string)(isset($dn['country']) ? $dn['country'] : '')), 0, 2)),
            'stateOrProvinceName'    => trim((string)(isset($dn['state']) ? $dn['state'] : '')),
            'localityName'           => trim((string)(isset($dn['city']) ? $dn['city'] : '')),
            'organizationName'       => trim((string)(isset($dn['organization']) ? $dn['organization'] : '')),
            'organizationalUnitName' => trim((string)(isset($dn['unit']) ? $dn['unit'] : '')),
            'commonName'             => strtolower(trim((string)(isset($dn['commonName']) ? $dn['commonName'] : ''))),
            'emailAddress'           => trim((string)(isset($dn['email']) ? $dn['email'] : '')),
        ], 'strlen');

        if(empty($name['commonName']) || !preg_match('/^(\*\.)?([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z0-9-]{2,}$/', $name['commonName']))
        {
            throw new \Exception('Enter a valid domain name as the common name.');
        }
        $key = openssl_pkey_new(['private_key_bits' => $bits, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if(!$key || !($csr = openssl_csr_new($name, $key, ['digest_alg' => 'sha256'])) || !openssl_csr_export($csr, $csrPem) || !openssl_pkey_export($key, $keyPem))
        {
            throw new \Exception('The CSR could not be generated.');
        }

        return ['csr' => $csrPem, 'key' => $keyPem];
    }

    // ---- SSL checker ---- //

    /**
     * Connects to the host and reads the certificate chain it presents. Private and reserved addresses are refused, the
     * connection goes to the checked address (no second DNS lookup).
     * @throws \Exception
     */
    public static function check($host, $port = 443)
    {
        $host = strtolower(trim((string)$host));
        $port = (int)$port;
        if(!preg_match('/^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z0-9-]{2,}$/', $host))
        {
            throw new \Exception('Enter a host name such as www.example.com.');
        }
        if(!in_array($port, self::CHECK_PORTS, true))
        {
            throw new \Exception('Port ' . $port . ' can not be checked.');
        }

        $address = null;
        foreach((array)@dns_get_record($host, DNS_A) as $record)
        {
            if(!empty($record['ip']) && filter_var($record['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            {
                $address = $record['ip'];
                break;
            }
        }
        if($address === null)
        {
            throw new \Exception('The host name does not resolve to a public address.');
        }

        $context = stream_context_create(['ssl' => [
            'capture_peer_cert'       => true,
            'capture_peer_cert_chain' => true,
            'verify_peer'             => false,
            'verify_peer_name'        => false,
            'SNI_enabled'             => true,
            'peer_name'               => $host,
        ]]);
        $socket = @stream_socket_client('ssl://' . $address . ':' . $port, $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $context);
        if(!$socket)
        {
            throw new \Exception('Could not connect to ' . $host . ':' . $port . ' (' . ($errstr ?: 'no answer') . ').');
        }
        $params = stream_context_get_params($socket);
        fclose($socket);

        $chain = [];
        foreach(isset($params['options']['ssl']['peer_certificate_chain']) ? $params['options']['ssl']['peer_certificate_chain'] : [] as $certificate)
        {
            openssl_x509_export($certificate, $pem);
            $chain[] = self::decodeCertificate($pem);
        }
        if(!$chain)
        {
            throw new \Exception('The server did not present a certificate.');
        }

        $names   = array_merge($chain[0]['sans'], isset($chain[0]['subject']['CN']) ? [$chain[0]['subject']['CN']] : []);
        $matches = false;
        foreach($names as $name)
        {
            $name = strtolower($name);
            if($name === $host || (strpos($name, '*.') === 0 && substr_count($host, '.') === substr_count($name, '.') && substr($host, strpos($host, '.')) === substr($name, 1)))
            {
                $matches = true;
            }
        }

        return ['host' => $host, 'port' => $port, 'address' => $address, 'chain' => $chain, 'hostMatches' => $matches];
    }

    // ---- helpers ---- //

    /**
     * The text trimmed and, when it is only the Base64 body, wrapped in the PEM header and footer
     */
    protected static function pem($text, $label)
    {
        $text = trim(str_replace("\r", '', (string)$text));
        if($text !== '' && strpos($text, '-----BEGIN') === false)
        {
            $text = "-----BEGIN $label-----\n" . chunk_split(preg_replace('/\s+/', '', $text), 64, "\n") . "-----END $label-----";
        }

        return $text;
    }

    protected static function derToPem($der)
    {
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END CERTIFICATE-----\n";
    }

    protected static function flatten($name)
    {
        $result = [];
        foreach((array)$name as $key => $value)
        {
            $result[$key] = is_array($value) ? implode(', ', $value) : (string)$value;
        }

        return $result;
    }

    protected static function keyType(array $details)
    {
        if(!isset($details['type']))
        {
            return '';
        }
        $types = [OPENSSL_KEYTYPE_RSA => 'RSA', OPENSSL_KEYTYPE_EC => 'EC', OPENSSL_KEYTYPE_DSA => 'DSA'];

        return isset($types[$details['type']]) ? $types[$details['type']] : 'other';
    }

    protected static function fingerprint($key)
    {
        $details = openssl_pkey_get_details($key);

        return hash('sha256', isset($details['key']) ? preg_replace('/\s+/', '', $details['key']) : '');
    }

    /**
     * Subject alternative names of a CSR: PHP has no function for them, so the DER of the request is searched for the
     * subjectAltName extension (OID 2.5.29.17) and its dNSName / iPAddress entries are read
     */
    protected static function csrSans($pem)
    {
        $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem));
        $pos = strpos($der, "\x06\x03\x55\x1d\x11");
        if($der === false || $pos === false)
        {
            return [];
        }

        // after the OID: [BOOLEAN critical] OCTET STRING { SEQUENCE { GeneralName ... } }
        $offset = $pos + 5;
        if(isset($der[$offset]) && $der[$offset] === "\x01")
        {
            $offset += 3;
        }
        if(!isset($der[$offset]) || $der[$offset] !== "\x04")
        {
            return [];
        }
        $offset++;
        self::readLength($der, $offset);
        if(!isset($der[$offset]) || $der[$offset] !== "\x30")
        {
            return [];
        }
        $offset++;
        $length = self::readLength($der, $offset);
        $end    = $offset + $length;

        $sans = [];
        while($offset < $end && isset($der[$offset]))
        {
            $tag = ord($der[$offset++]);
            $len = self::readLength($der, $offset);
            $val = substr($der, $offset, $len);
            $offset += $len;
            if($tag === 0x82)
            {
                $sans[] = $val;
            }
            elseif($tag === 0x87 && ($len === 4 || $len === 16))
            {
                $sans[] = inet_ntop($val);
            }
        }

        return $sans;
    }

    protected static function readLength($der, &$offset)
    {
        $first = ord($der[$offset++]);
        if($first < 0x80)
        {
            return $first;
        }
        $bytes  = $first & 0x7f;
        $length = 0;
        for($i = 0; $i < $bytes; $i++)
        {
            $length = ($length << 8) | ord($der[$offset++]);
        }

        return $length;
    }
}
