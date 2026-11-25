<?php

namespace TSSGGSModule\App\Libs\Certificates\Concerns;

use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use TSSGGSModule\App\Libs\Certificates\CertificateService;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Libs\Certificates\PrivateKeyDownload;

/**
 * Private keys stored for a certificate: generated, pasted or from AutoInstall, and their removal.
 * Part of CertificateService (used there as a trait).
 */
trait PrivateKeys
{
    /**
     * Cancelled / terminated service: the private keys kept for it are deleted (kept key and the AutoInstall copy)
     */
    public static function forgetPrivateKeys($serviceId)
    {
        try
        {
            $deleted = Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->delete();
            $state   = AutoInstallHelpers::get($serviceId);
            if($state && !empty($state['private_key']))
            {
                $status = $state['status'];
                unset($state['private_key']);
                AutoInstallQueue::cancelOpenTasks($serviceId, 'Service cancelled');
                AutoInstallHelpers::save($serviceId, $state, $status === AutoInstallHelpers::STATUS_INSTALLED ? $status : AutoInstallHelpers::STATUS_DISABLED);
                $deleted = true;
            }
            if($deleted)
            {
                self::event($serviceId, 'info', 'Stored private key deleted (service cancelled or terminated)');
            }
        }
        catch(\Throwable $e)
        {
        }
    }

    /**
     * Keeps the private key of the service encrypted, so "Install on server" does not ask the client for it.
     * Only keys the module generated (or the client pasted for an installation) are kept.
     */
    public static function storePrivateKey($serviceId, $pem)
    {
        $pem = trim((string)$pem);
        if($pem === '' || !@openssl_pkey_get_private($pem))
        {
            return;
        }
        Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->delete();
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => self::PRIVATE_KEY_REQUEST,
            'request'    => \encrypt($pem),
            'status'     => 'stored',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * A CSR the client made with the module's CSR generator in this session: keep its private key
     */
    public static function rememberGeneratedKey($serviceId, $csr)
    {
        $pem = PrivateKeyDownload::matching($serviceId, $csr);
        if($pem !== null)
        {
            self::storePrivateKey($serviceId, $pem);
        }
    }

    /**
     * The stored private key (AutoInstall or kept above) that belongs to the current certificate, '' when none does
     * (e.g. the certificate was reissued with the client's own CSR)
     */
    public static function privateKeyForCertificate($serviceId)
    {
        try
        {
            $crt = self::bundle($serviceId)->crt();
        }
        catch(\Exception $e)
        {
            return '';
        }

        $candidates = [];
        $state      = AutoInstallHelpers::get($serviceId);
        if(!empty($state['private_key']))
        {
            $candidates[] = \decrypt($state['private_key']);
        }
        $row = Request::where('serviceid', (int)$serviceId)->where('name', self::PRIVATE_KEY_REQUEST)->first();
        if($row && $row->request)
        {
            $candidates[] = \decrypt($row->request);
        }
        foreach($candidates as $pem)
        {
            $pem = trim((string)$pem);
            if($pem !== '' && @openssl_x509_check_private_key($crt, $pem))
            {
                return $pem;
            }
        }

        return '';
    }

    /**
     * CSR made from the AutoInstall private key (same public key)
     */
    public static function csrUsesAutoInstallKey($serviceId, $csr)
    {
        $state = AutoInstallHelpers::get($serviceId);
        if(empty($state['private_key']) || $state['status'] === AutoInstallHelpers::STATUS_DISABLED)
        {
            return false;
        }

        $key       = @openssl_pkey_get_private(\decrypt($state['private_key']));
        $csrPublic = @openssl_csr_get_public_key($csr);
        if(!$key || !$csrPublic)
        {
            return false;
        }

        return openssl_pkey_get_details($key)['key'] === openssl_pkey_get_details($csrPublic)['key'];
    }

    /**
     * New CSR for a reissue, signed with the private key stored for AutoInstall
     */
    public static function csrFromAutoInstallKey($serviceId, $commonName)
    {
        $state = AutoInstallHelpers::get($serviceId);
        $key   = !empty($state['private_key']) ? @openssl_pkey_get_private(\decrypt($state['private_key'])) : false;
        if(!$key)
        {
            throw new \Exception('No private key is stored for the automatic installation of this certificate');
        }

        $csr = openssl_csr_new(['commonName' => $commonName], $key, ['digest_alg' => 'sha256']);
        if(!$csr || !openssl_csr_export($csr, $pem))
        {
            throw new \Exception('The CSR could not be generated');
        }

        return $pem;
    }
}
