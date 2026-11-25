<?php

namespace TSSGGSModule\App\Libs\Gateway\Endpoints;

use TSSGGSModule\App\Libs\ProductLines\DocumentSigningService;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;

/**
 * Orders of the other certificate products: S/MIME, code signing, document signing, PCI, LEI.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait ProductOrders
{
    /**
     * S/MIME order: product {id, term}, emails (first = subject), optional csr and contacts (OV products)
     */
    public function createSmime(array $data)
    {
        return $this->call('/certificates/smime', $data);
    }

    /**
     * Code signing order: product {id, term}, provisioning {method, model | csr | shipping {id, contact}}, contacts
     */
    public function createCodeSigning(array $data)
    {
        return $this->call('/certificates/cs', $data);
    }

    /**
     * Document signing order: product {id, term}, provisioning {method, model | shipping {id, contact}}, contacts
     */
    public function createDocumentSigning(array $data)
    {
        return $this->call('/certificates/' . DocumentSigningService::CATEGORY, $data);
    }

    // ------------------------------------------------------------------ HackerGuardian PCI (TheSSLStore only)

    /** POST /pci: the scan account, active at once; the answer carries its login (items[0].login), the only time it is sent */
    public function createPci(array $data)
    {
        return $this->call('/pci', $data);
    }

    // ------------------------------------------------------------------ LEI codes (GoGetSSL only for now)

    /** POST /lei: a new LEI registration, paid at once */
    public function createLei(array $data)
    {
        return $this->call('/lei', $data);
    }

    /**
     * POST /lei/{id}/confirm (no body, answers 204): the client confirms the data the issuer is about to register -
     * only while the LEI record waits for it (items[].status need_action), else 400 GGS-3
     */
    public function confirmLei($leiId)
    {
        return $this->call('/lei/' . (int)$leiId . '/confirm', [], 'POST');
    }

    /** POST /lei/{id}/renew: the renewal order of an issued LEI; the answer is the new LEI record */
    public function renewLei($leiId, array $data)
    {
        return $this->call('/lei/' . (int)$leiId . '/renew', $data);
    }

    // ------------------------------------------------------------------ CodeGuard (website backup, TheSSLStore only)
}
