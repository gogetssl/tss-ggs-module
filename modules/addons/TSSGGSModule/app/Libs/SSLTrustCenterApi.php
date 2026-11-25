<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\Endpoints;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Client of the gateway API (TheSSLStore / GoGetSSL reseller API v2), one instance per vendor account and product
 * category. The endpoints are grouped by product line in App\Libs\Gateway\Endpoints; the HTTP request, logging and
 * error handling are in Endpoints\Transport. Errors are thrown as Gateway\GatewayException (the gateway's error code
 * and raw message kept), an order still waiting for its validation method as Gateway\AwaitingConfigurationException.
 */
class SSLTrustCenterApi
{
    use Endpoints\Transport;
    use Endpoints\Catalogue;
    use Endpoints\TlsOrders;
    use Endpoints\Subscriptions;
    use Endpoints\ProductOrders;
    use Endpoints\CodeGuard;

    const API_URL = 'https://my.gogetssl.com/whmcs/v2';
    // the gateway's sandbox: the sandbox credentials are only accepted there (the live host answers 401 AUX-1)
    const SANDBOX_URL = 'https://sandbox.gogetssl.com/whmcs/v2';

    // A hung gateway must not stall the crons or the client area pages (seconds)
    const CONNECT_TIMEOUT = 10;
    const TIMEOUT         = 60;

    /** categories of the subscription products */
    const SUBSCRIPTION_CATEGORIES = ['caas', 'ais'];

    /** gateway category of the CodeGuard products, and the base path of their own endpoints */
    const CODE_GUARD_CATEGORY = 'code-guard';

    /** the gateway's message for an order whose validation method was never chosen (502 GEX-4) */
    const AWAITING_CONFIGURATION = 'validation method: not yet set';

    protected $apiUrl = self::API_URL;
    protected $key;
    protected $code;
    protected $type;
    protected $category;
    protected $serviceHint = null;

    /** seconds this client waits for an answer (TIMEOUT unless set shorter with withTimeout) */
    protected $timeout = self::TIMEOUT;

    /** an admin page that asks the gateway while it loads waits at most this long (seconds) */
    const PAGE_TIMEOUT = 8;

    /**
     * @param bool $sandbox true: the sandbox credentials, sent to the sandbox gateway (SANDBOX_URL)
     */
    public function __construct($type, $code, $key, $category = 'tls', $sandbox = false)
    {
        $this->type     = $type;
        $this->code     = $code;
        $this->key      = $key;
        $this->category = $category;
        $this->apiUrl   = $sandbox ? self::SANDBOX_URL : self::API_URL;
    }

    /**
     * A shorter wait for checks made while a page loads (connection status, balance): a slow gateway then costs the
     * page seconds, not a minute. Orders and other actions keep the full TIMEOUT.
     */
    public function withTimeout(int $seconds): self
    {
        $this->timeout = max(1, min($seconds, self::TIMEOUT));

        return $this;
    }

    /** true when this client talks to the sandbox gateway */
    public function isSandbox()
    {
        return $this->apiUrl === self::SANDBOX_URL;
    }

    /** the gateway URL for a mode */
    public static function urlFor($sandbox)
    {
        return $sandbox ? self::SANDBOX_URL : self::API_URL;
    }

    /**
     * [partner code, auth token, sandbox] of a vendor as stored in the module settings (TheSSLStore: the sandbox pair
     * while the sandbox mode is on, used with the sandbox gateway); null when the vendor has no credentials
     */
    public static function credentialsOf($vendor)
    {
        $vendor      = strtolower((string)$vendor);
        $credentials = (new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['credentials'][$vendor] ?? [];
        $sandbox     = $vendor === 'tss' && ($credentials['OperationMode'] ?? '') === 'sandbox';
        $code        = trim((string)($credentials[$sandbox ? 'TestPartnerCode' : 'PartnerCode'] ?? ''));
        $token       = (string)($credentials[$sandbox ? 'TestAuthToken' : 'AuthToken'] ?? '');

        return $code !== '' ? [$code, $token, $sandbox] : null;
    }

    /**
     * Gateway client of a vendor for a product category
     */
    public static function forVendor($vendor, $category = 'tls')
    {
        $credentials = self::credentialsOf($vendor);
        if($credentials === null)
        {
            throw new \Exception('No API credentials configured for ' . Helpers::vendorToDisplay($vendor));
        }

        return new self($vendor, $credentials[0], $credentials[1], $category ?: 'tls', $credentials[2]);
    }
}
