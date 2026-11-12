<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptionsGroup;
use WHMCS\Database\Capsule;

/**
 * Hardware code signing certificates of GoGetSSL, ordered through the gateway (/certificates/cs).
 *
 * Delivery ("provisioning"): a new hardware token shipped by the CA, the client's existing token or the client's own
 * HSM (CSR). The token shipping is charged to the reseller, so it is sold as the "Certificate Delivery" configurable
 * option of the product (one-time fee per shipping option); a renewal reuses the token (no shipping on the invoice).
 * Cloud code signing ("keylocker") is not supported.
 *
 * Stored order (see GatewayOrderService) plus: method, tokenModel, shipping, csrProvided, organization.
 * tblhosting.domain = the organization name.
 */
class CodeSigningService extends GatewayOrderService
{
    const CATEGORY     = 'cs';
    const REQUEST_NAME = 'codesigning';
    const LABEL        = 'Code Signing';

    // GoGetSSL products the module sells (the Sectigo ones and cloud code signing are not supported)
    const SUPPORTED_PRODUCTS = ['GGS' => ['39', '89', '137', '138']];

    const METHOD_SHIP_TOKEN     = 'ship-token';
    const METHOD_EXISTING_TOKEN = 'existing-token';
    const METHOD_HSM            = 'hsm';
    const METHODS               = [self::METHOD_SHIP_TOKEN, self::METHOD_EXISTING_TOKEN, self::METHOD_HSM];

    // token models for "existing-token": the values the gateway accepts (the API v2 documentation shows TOKEN_5110_*,
    // the API itself requires SAFENET_5110_*)
    const TOKEN_MODELS = [
        'SAFENET_5110_FIPS'      => 'SafeNet eToken 5110 FIPS',
        'SAFENET_5110_CC'        => 'SafeNet eToken 5110 CC',
        'SAFENET_5110_PLUS_FIPS' => 'SafeNet eToken 5110+ FIPS',
    ];

    const DEFAULT_SHIPPING = 'STANDARD';

    // configurable option of the product; sub-option values "ship-token:<SHIPPING ID>", "existing-token", "hsm"
    const OPTION_PROVISIONING = 'cs_provisioning';
    const OPTION_LABEL        = 'Certificate Delivery';

    const ORGANIZATION_FIELDS    = ['name', 'phone', 'address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'];
    const ORGANIZATION_REQUIRED  = ['name', 'phone', 'address_line_1', 'city', 'region', 'country', 'postal_code'];
    const ADMINISTRATOR_FIELDS   = ['first_name', 'last_name', 'title', 'email', 'phone', 'address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'];
    const ADMINISTRATOR_REQUIRED = ['first_name', 'last_name', 'title', 'email', 'phone', 'address_line_1', 'city', 'region', 'country', 'postal_code'];
    const SHIPPING_FIELDS        = ['first_name', 'last_name', 'address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'];
    const SHIPPING_REQUIRED      = ['first_name', 'last_name', 'address_line_1', 'city', 'region', 'country', 'postal_code'];

    public static function configurationTemplate()
    {
        return EmailTemplates::CS_CONFIGURATION_TEMPLATE;
    }

    public static function isCodeSigningWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isCodeSigningService($serviceId)
    {
        return static::isService($serviceId);
    }

    /**
     * Gateway products the module can sell: the GoGetSSL hardware code signing products
     */
    public static function isSupportedRemoteProduct($vendor, $category, $remoteId)
    {
        return strtolower((string)$category) === self::CATEGORY
            && in_array((string)$remoteId, self::SUPPORTED_PRODUCTS[strtoupper((string)$vendor)] ?? [], true);
    }

    // ------------------------------------------------------------------ product data

    /**
     * Hardware delivery methods of the gateway product
     */
    public static function methods(array $rawData)
    {
        $methods = isset($rawData['provisioning']['methods']) && is_array($rawData['provisioning']['methods']) ? $rawData['provisioning']['methods'] : self::METHODS;

        return array_values(array_intersect(self::METHODS, array_map('strval', $methods)));
    }

    /**
     * Token shipping options of the gateway product: [['id' => 'STANDARD', 'label' => .., 'selling' => float], ...],
     * STANDARD first
     */
    public static function shippingOptions(array $rawData)
    {
        $options = [];
        foreach((array)($rawData['tokens'] ?? []) as $token)
        {
            foreach((array)($token['shipping_options'] ?? []) as $option)
            {
                if(empty($option['id']))
                {
                    continue;
                }
                $id = strtoupper((string)$option['id']);
                $options[$id] = [
                    'id'      => $id,
                    'label'   => !empty($option['label']) ? (string)$option['label'] : ucfirst(strtolower($id)) . ' Shipping',
                    'selling' => is_numeric($option['selling'] ?? null) ? (float)$option['selling'] : 0.0,
                ];
            }
        }
        uksort($options, function($a, $b) {
            return ($b === self::DEFAULT_SHIPPING) - ($a === self::DEFAULT_SHIPPING);
        });

        return array_values($options);
    }

    /**
     * "ship-token:EXPEDITED" -> ['method' => 'ship-token', 'shipping' => 'EXPEDITED'], null when not valid
     */
    public static function parseProvisioningValue($value)
    {
        $parts  = explode(':', strtolower(trim((string)$value)), 2);
        $method = $parts[0];
        if(!in_array($method, self::METHODS, true))
        {
            return null;
        }

        return [
            'method'   => $method,
            'shipping' => $method === self::METHOD_SHIP_TOKEN ? (isset($parts[1]) && $parts[1] !== '' ? strtoupper($parts[1]) : self::DEFAULT_SHIPPING) : null,
        ];
    }

    // ------------------------------------------------------------------ "Certificate Delivery" configurable option

    /** The delivery option of the product (null when it has none) */
    public static function deliveryOption($whmcsProductId)
    {
        return Capsule::table('tblproductconfigoptions')
            ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
            ->where('tblproductconfiglinks.pid', (int)$whmcsProductId)
            ->where('tblproductconfigoptions.optionname', 'like', self::OPTION_PROVISIONING . '|%')
            ->first(['tblproductconfigoptions.id', 'tblproductconfigoptions.gid']);
    }

    /**
     * Creates the "Certificate Delivery" dropdown of an imported product: one choice per shipping option (the shipping
     * price as a one-time fee), "existing token" and "own HSM" (free). The administrator limits the choices by hiding
     * sub-options in WHMCS.
     */
    public static function createDeliveryOption($whmcsProductId, array $rawData)
    {
        if(static::deliveryOption($whmcsProductId))
        {
            return;
        }

        $product = Product::find((int)$whmcsProductId);
        if(!$product)
        {
            return;
        }

        $methods = static::methods($rawData);
        $subs    = [];
        if(in_array(self::METHOD_SHIP_TOKEN, $methods, true))
        {
            // no shipping price from the gateway = nothing to charge: no "new token" choice
            foreach(static::shippingOptions($rawData) as $shipping)
            {
                $subs[] = ['ship-token:' . $shipping['id'], 'New hardware token - ' . $shipping['label'], $shipping['selling']];
            }
        }
        if(in_array(self::METHOD_EXISTING_TOKEN, $methods, true))
        {
            $subs[] = ['existing-token', 'I already have a compatible hardware token', 0.0];
        }
        if(in_array(self::METHOD_HSM, $methods, true))
        {
            $subs[] = ['hsm', 'My own HSM (I will provide a CSR)', 0.0];
        }
        if(!$subs)
        {
            return;
        }

        $group    = (new ConfigurableOptionsGroup())->getFirstOrCreateRelated($product);
        $optionId = Capsule::table('tblproductconfigoptions')->insertGetId([
            'gid'        => (int)$group->id,
            'optionname' => self::OPTION_PROVISIONING . '|' . self::OPTION_LABEL,
            'optiontype' => 1,
            'qtyminimum' => 0,
            'qtymaximum' => 0,
            'order'      => 0,
            'hidden'     => 0,
        ]);
        foreach($subs as $sortOrder => list($value, $label, $cost))
        {
            $subId = Capsule::table('tblproductconfigoptionssub')->insertGetId([
                'configid'   => $optionId,
                'optionname' => $value . '|' . $label,
                'sortorder'  => $sortOrder,
                'hidden'     => 0,
            ]);
            foreach(Capsule::table('tblcurrencies')->pluck('id') as $currencyId)
            {
                Capsule::table('tblpricing')->insert(['type' => 'configoptions', 'currency' => (int)$currencyId, 'relid' => $subId] + static::oneTimeFee($cost, (int)$currencyId));
            }
        }
    }

    /**
     * Shipping fees of the delivery option from the gateway prices (price update cron)
     */
    public static function syncDeliveryPrices($whmcsProductId, array $rawData)
    {
        $option = static::deliveryOption($whmcsProductId);
        if(!$option)
        {
            return;
        }

        $prices = [];
        foreach(static::shippingOptions($rawData) as $shipping)
        {
            $prices['ship-token:' . strtolower($shipping['id'])] = $shipping['selling'];
        }
        foreach(Capsule::table('tblproductconfigoptionssub')->where('configid', $option->id)->get(['id', 'optionname']) as $sub)
        {
            $value = strtolower(trim(explode('|', (string)$sub->optionname, 2)[0]));
            if(!isset($prices[$value]))
            {
                continue;
            }
            foreach(Capsule::table('tblcurrencies')->pluck('id') as $currencyId)
            {
                Capsule::table('tblpricing')->where('type', 'configoptions')->where('relid', $sub->id)->where('currency', (int)$currencyId)
                    ->update(static::oneTimeFee($prices[$value], (int)$currencyId));
            }
        }
    }

    /**
     * A one-time fee as WHMCS configurable option pricing: the setup fee of every billing cycle, no recurring price.
     * Gateway cost (USD) x the module's exchange rate, in the currency (no margin: the shipping is passed on at cost).
     */
    protected static function oneTimeFee($cost, $currencyId)
    {
        $rate = (float)((new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['financeSettings']['rate'] ?? 1) ?: 1;
        $fee  = number_format(FinancePolicy::toCurrency((float)$cost * $rate, $currencyId), 2, '.', '');

        $pricing = [];
        foreach(['m', 'q', 's', 'a', 'b', 't'] as $prefix)
        {
            $pricing[$prefix . 'setupfee'] = $fee;
        }
        foreach(['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'] as $cycle)
        {
            $pricing[$cycle] = '0.00';
        }

        return $pricing;
    }

    /**
     * The delivery the client paid for with the service ("Certificate Delivery" option), null when the service has
     * no such option
     */
    public static function paidDelivery($serviceId)
    {
        $value = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
            ->where('tblhostingconfigoptions.relid', (int)$serviceId)
            ->where('tblproductconfigoptions.optionname', 'like', self::OPTION_PROVISIONING . '|%')
            ->value('tblproductconfigoptionssub.optionname');

        return $value === null ? null : trim(explode('|', (string)$value, 2)[0]);
    }

    /**
     * Delivery methods the client may choose:
     * - the method of the "Certificate Delivery" option (paid with the order) is fixed;
     * - a renewal, or a service without that option: the client's existing token or own HSM (a new token is shipped
     *   only when its shipping was paid; the gateway charges the shipping to the reseller).
     * A paid option the gateway product no longer offers stops the order (error key "csProvisioningInvalid").
     *
     * @return array ['methods' => string[], 'fixed' => ['method' => .., 'shipping' => ..] | null, 'shipping_options' => array, 'renewal' => bool]
     */
    public static function availableDelivery($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        $remote    = static::remoteProduct($packageId);
        $rawData   = $remote && is_array($remote->rawData) ? $remote->rawData : [];
        $methods   = static::methods($rawData);
        $renewal   = !empty(static::stored($serviceId)['previous']);

        $paid  = static::paidDelivery($serviceId);
        $fixed = $paid !== null && $paid !== '' ? static::parseProvisioningValue($paid) : null;
        if($paid !== null && $paid !== '' && ($fixed === null || !in_array($fixed['method'], $methods, true)))
        {
            throw new \Exception('csProvisioningInvalid');
        }

        if($fixed !== null && !$renewal)
        {
            return ['methods' => [$fixed['method']], 'fixed' => $fixed, 'shipping_options' => static::shippingOptions($rawData), 'renewal' => false];
        }

        return [
            'methods'          => array_values(array_diff($methods, [self::METHOD_SHIP_TOKEN])),
            'fixed'            => null,
            'shipping_options' => static::shippingOptions($rawData),
            'renewal'          => $renewal,
        ];
    }

    // ------------------------------------------------------------------ order

    /**
     * Checks the client's input and builds the order data.
     *
     * @param array $input method, model, csr, organization, administrator (same_address), shipping (same_address)
     * @return array ['provisioning' => .., 'contacts' => ..]
     * @throws CodeSigningInputException with a language key (and an argument) the client area translates
     */
    public static function buildOrder($serviceId, array $input)
    {
        try
        {
            $available = static::availableDelivery($serviceId);
        }
        catch(\Exception $e)
        {
            throw new CodeSigningInputException($e->getMessage());
        }

        if($available['fixed'] !== null)
        {
            $method   = $available['fixed']['method'];
            $shipping = $available['fixed']['shipping'];
        }
        else
        {
            $method   = strtolower(trim((string)($input['method'] ?? '')));
            $shipping = null;
        }
        if(!in_array($method, $available['methods'], true))
        {
            throw new CodeSigningInputException('csInvalidMethod');
        }

        $provisioning = ['method' => $method];
        if($method === self::METHOD_EXISTING_TOKEN)
        {
            $model = strtoupper(trim((string)($input['model'] ?? '')));
            if(!isset(self::TOKEN_MODELS[$model]))
            {
                throw new CodeSigningInputException('csTokenModelRequired');
            }
            $provisioning['model'] = $model;
        }
        elseif($method === self::METHOD_HSM)
        {
            $csr = trim((string)($input['csr'] ?? ''));
            if(strpos($csr, '-----BEGIN CERTIFICATE REQUEST-----') === false || @openssl_csr_get_subject($csr) === false)
            {
                throw new CodeSigningInputException('csInvalidCsr');
            }
            $provisioning['csr'] = $csr;
        }
        else
        {
            $ids = array_column($available['shipping_options'], 'id');
            if($ids && !in_array($shipping, $ids, true))
            {
                throw new CodeSigningInputException('csShippingNotAvailable', $shipping);
            }
            $provisioning['shipping'] = ['id' => $shipping];
        }

        $contacts = static::contacts((array)($input['organization'] ?? []), (array)($input['administrator'] ?? []));
        if($method === self::METHOD_SHIP_TOKEN)
        {
            $provisioning['shipping']['contact'] = static::shippingContact((array)($input['shipping'] ?? []), $contacts);
        }

        return ['provisioning' => $provisioning, 'contacts' => $contacts];
    }

    /**
     * Organization and administrator (all fields the gateway requires). The administrator works at the organization
     * address unless an own address is given.
     */
    public static function contacts(array $organization, array $administrator)
    {
        $org = [];
        foreach(self::ORGANIZATION_FIELDS as $field)
        {
            $org[$field] = trim((string)($organization[$field] ?? ''));
        }
        $admin = [];
        foreach(self::ADMINISTRATOR_FIELDS as $field)
        {
            $admin[$field] = trim((string)($administrator[$field] ?? ''));
        }
        if(!empty($administrator['same_address']) || $admin['address_line_1'] === '')
        {
            foreach(['address_line_1', 'address_line_2', 'city', 'region', 'country', 'postal_code'] as $field)
            {
                $admin[$field] = $org[$field];
            }
        }

        foreach(self::ORGANIZATION_REQUIRED as $field)
        {
            if($org[$field] === '')
            {
                throw new CodeSigningInputException('csFieldRequired', 'csOrg_' . $field);
            }
        }
        foreach(self::ADMINISTRATOR_REQUIRED as $field)
        {
            if($admin[$field] === '')
            {
                throw new CodeSigningInputException('csFieldRequired', 'csAdmin_' . $field);
            }
        }
        if(filter_var($admin['email'], FILTER_VALIDATE_EMAIL) === false)
        {
            throw new CodeSigningInputException('csInvalidEmail', $admin['email']);
        }
        $org['country']   = static::country($org['country']);
        $admin['country'] = static::country($admin['country']);

        // the gateway takes no empty optional fields
        return ['organization' => array_filter($org, 'strlen'), 'administrator' => array_filter($admin, 'strlen')];
    }

    /**
     * Token recipient: the administrator at the organization address unless another address is given
     */
    public static function shippingContact(array $contact, array $contacts)
    {
        $result = [];
        foreach(self::SHIPPING_FIELDS as $field)
        {
            $result[$field] = trim((string)($contact[$field] ?? ''));
        }
        if(!empty($contact['same_address']) || $result['address_line_1'] === '')
        {
            $org    = $contacts['organization'];
            $result = [
                'first_name'     => $contacts['administrator']['first_name'],
                'last_name'      => $contacts['administrator']['last_name'],
                'address_line_1' => $org['address_line_1'],
                'address_line_2' => $org['address_line_2'] ?? '',
                'city'           => $org['city'],
                'region'         => $org['region'],
                'country'        => $org['country'],
                'postal_code'    => $org['postal_code'],
            ];
        }
        foreach(self::SHIPPING_REQUIRED as $field)
        {
            if($result[$field] === '')
            {
                throw new CodeSigningInputException('csFieldRequired', 'csShip_' . $field);
            }
        }
        $result['country'] = static::country($result['country']);

        return array_filter($result, 'strlen');
    }

    protected static function country($country)
    {
        $country = strtoupper(trim((string)$country));
        if(!preg_match('/^[A-Z]{2}$/', $country))
        {
            throw new CodeSigningInputException('csInvalidCountry', $country);
        }

        return $country;
    }

    /**
     * Places the order at the gateway (input checked by buildOrder()).
     */
    public static function create($serviceId, array $order)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::termForBillingCycle($service->billingcycle, static::availableTerms($service->packageid));
        $payload   = [
            'product'      => ['id' => $productId, 'term' => $term],
            'provisioning' => $order['provisioning'],
            'contacts'     => $order['contacts'],
        ];

        // one order per service: a second submit (double click, two tabs) must not order again
        $locked = Capsule::table('tblsslorders')->where('id', $ssl->id)->where('status', 'Awaiting Configuration')->update(['status' => 'Configuration Submitted']);
        if(!$locked)
        {
            throw new \Exception('The code signing certificate has already been ordered');
        }
        try
        {
            $response = CertificateService::api($serviceId)->createCodeSigning($payload);
        }
        catch(\Exception $e)
        {
            // not ordered: the client can correct the data and submit again
            Capsule::table('tblsslorders')->where('id', $ssl->id)->update(['status' => 'Awaiting Configuration']);
            throw $e;
        }

        $provisioning = $order['provisioning'];
        $organization = (string)$order['contacts']['organization']['name'];
        // the order details without the CSR, for "renew with the same details"
        $request = $payload;
        unset($request['provisioning']['csr']);

        return static::saveCreated($serviceId, $ssl, [
            'request'      => $request,
            'term'         => $term,
            'method'       => $provisioning['method'],
            'tokenModel'   => $provisioning['model'] ?? '',
            'shipping'     => $provisioning['shipping']['id'] ?? '',
            'csrProvided'  => isset($provisioning['csr']),
            'organization' => $organization,
        ], $response, $organization, 'Code signing certificate ordered for ' . $organization . ' (' . static::describeDelivery($provisioning) . ')');
    }

    /**
     * "Same details" works for a token (a renewal ships no new one: an order with a new token renews for that token,
     * its model is asked in the Renew dialog); an HSM needs a new key pair, so a new CSR
     */
    public static function canRenewWithSameDetails(array $stored)
    {
        $method = (string)($stored['request']['provisioning']['method'] ?? '');

        return !empty($stored['request']) && in_array($method, [self::METHOD_EXISTING_TOKEN, self::METHOD_SHIP_TOKEN], true);
    }

    /** The Renew dialog asks for the token model (the first order shipped a new token) */
    public static function renewalNeedsTokenModel(array $stored)
    {
        return (string)($stored['request']['provisioning']['method'] ?? '') === self::METHOD_SHIP_TOKEN;
    }

    protected static function renewWithSameDetails($serviceId, array $before)
    {
        if(!static::canRenewWithSameDetails($before))
        {
            return false;
        }
        $request = (array)$before['request'];
        $model   = (string)($request['provisioning']['model'] ?? '');
        if(static::renewalNeedsTokenModel($before))
        {
            $model = strtoupper((string)($before['renewExtra']['model'] ?? ''));
        }
        if(!isset(self::TOKEN_MODELS[$model]))
        {
            return false;
        }

        static::create($serviceId, [
            'provisioning' => ['method' => self::METHOD_EXISTING_TOKEN, 'model' => $model],
            'contacts'     => (array)$request['contacts'],
        ]);

        return true;
    }

    public static function describeDelivery(array $provisioning)
    {
        switch($provisioning['method'] ?? '')
        {
            case self::METHOD_SHIP_TOKEN:
                return 'new token, ' . strtolower($provisioning['shipping']['id'] ?? self::DEFAULT_SHIPPING) . ' shipping';
            case self::METHOD_EXISTING_TOKEN:
                return 'existing token ' . ($provisioning['model'] ?? '');
            case self::METHOD_HSM:
                return 'own HSM (CSR)';
        }

        return (string)($provisioning['method'] ?? '');
    }

    protected static function applyItem(array $stored, array $item)
    {
        if(!empty($item['vendor']['status']))
        {
            $stored['vendorStatus'] = (string)$item['vendor']['status'];
        }

        return $stored;
    }

    /**
     * "Code Signing Certificate Issued" e-mail: how the client gets the certificate (token shipped, install on the
     * existing token, download for the HSM)
     */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
        if(($stored['status'] ?? '') !== 'active')
        {
            return;
        }

        try
        {
            EmailTemplates::sendEmail(EmailTemplates::CS_ISSUED_TEMPLATE, $serviceId, [
                'cs_organization' => (string)($stored['organization'] ?? ''),
                'cs_valid_till'   => !empty($stored['validTill']) ? date('F j, Y', strtotime($stored['validTill'])) : '',
                'cs_method'       => (string)($stored['method'] ?? ''),
            ]);
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', 'Code signing issued e-mail not sent: ' . $e->getMessage());
        }
    }

    protected static function previousEntry(array $stored)
    {
        return ['organization' => $stored['organization'] ?? '', 'method' => $stored['method'] ?? ''];
    }
}
