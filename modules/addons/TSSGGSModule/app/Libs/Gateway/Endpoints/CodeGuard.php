<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Gateway\Endpoints;

use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;

/**
 * CodeGuard website backup licences.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait CodeGuard
{
    protected function codeGuardPath($licenseId, $suffix = '')
    {
        return '/' . self::CODE_GUARD_CATEGORY . '/' . (int)$licenseId . $suffix;
    }

    /**
     * CodeGuard order: product {id, term}, contacts {administrator {first_name, last_name, email}}. The licence is
     * created at once - there is no configuration step the client has to go through first.
     */
    public function createCodeGuard(array $data)
    {
        return $this->call('/' . self::CODE_GUARD_CATEGORY, $data);
    }

    public function getCodeGuardDetails($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId), [], 'GET');
    }

    /** one-time link into the CodeGuard dashboard (the client's only way in) */
    public function getCodeGuardSsoLink($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId, '/sso-link'), [], 'GET');
    }

    /** websites the licence backs up (gateway defect 2026-11-13: answers 500 while the licence has none) */
    public function getCodeGuardWebsites($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId, '/websites'), [], 'GET');
    }

    /** administrator contact of the licence: contacts {administrator {first_name, last_name, email}} */
    public function editCodeGuard($licenseId, array $data)
    {
        return $this->call($this->codeGuardPath($licenseId), $data, 'PATCH');
    }

    public function suspendCodeGuard($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId, '/suspend'), [], 'POST');
    }

    public function activateCodeGuard($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId, '/activate'), [], 'POST');
    }

    /**
     * Moves the licence to another plan. The gateway places a NEW order for the new plan (its id comes back in the
     * answer) and the vendor licence carries over; the superseded order's licence is gone.
     */
    public function upgradeCodeGuardProduct($licenseId, $productId)
    {
        return $this->call($this->codeGuardPath($licenseId, '/product'), ['product' => ['id' => self::productId($productId)]], 'PUT');
    }

    /**
     * Cancels the licence. The documented POST .../cancel-subscription does not exist (404, reported 2026-11-13);
     * DELETE works and leaves the order as "cancelled".
     */
    public function deleteCodeGuard($licenseId)
    {
        return $this->call($this->codeGuardPath($licenseId), [], 'DELETE');
    }
}
