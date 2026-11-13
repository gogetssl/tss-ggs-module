<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;

/**
 * Document signing certificates of TheSSLStore, ordered through the gateway (/certificates/ds).
 *
 * The certificate signs PDFs and Office documents and always lives on a hardware token: either one the certificate
 * authority ships, or one the client already owns. There is no HSM and no CSR - unlike code signing - and neither the
 * token nor its shipping is charged (both are 0 in the gateway product), so the delivery is simply asked in the order
 * form instead of being sold as a configurable option.
 *
 * All four products are DigiCert and TheSSLStore only; GoGetSSL does not resell them.
 *
 * Stored order (see GatewayOrderService) plus: method, tokenModel, shipping, organization, tokenSetupLink.
 * tblhosting.domain = the organization name.
 */
class DocumentSigningService extends GatewayOrderService
{
    const CATEGORY     = 'ds';
    const REQUEST_NAME = 'docsigning';
    const LABEL        = 'Document Signing';

    /** gateway products the module sells (TheSSLStore only) */
    const SUPPORTED_PRODUCTS = ['digi_doc_signing_ind_500', 'digi_doc_signing_ind_2000', 'digi_doc_signing_org_2000', 'digi_doc_signing_org_5000'];

    const METHOD_SHIP_TOKEN     = 'ship-token';
    const METHOD_EXISTING_TOKEN = 'existing-token';
    const METHODS               = [self::METHOD_SHIP_TOKEN, self::METHOD_EXISTING_TOKEN];

    /**
     * Token models the vendor accepts for an existing token. This list is NOT the one code signing uses - it was read
     * from the gateway's own error on 2026-11-13, and SAFENET_5110_CC (valid for code signing) is refused here.
     */
    const TOKEN_MODELS = [
        'SAFENET_5110_FIPS'          => 'SafeNet eToken 5110 FIPS',
        'SAFENET_5110'               => 'SafeNet eToken 5110',
        'SAFENET_5100'               => 'SafeNet eToken 5100',
        'SAFENET_5105'               => 'SafeNet eToken 5105',
        'SAFENET_5200'               => 'SafeNet eToken 5200',
        'SAFENET_5205'               => 'SafeNet eToken 5205',
        'SAFENET_PRO_72K'            => 'SafeNet eToken PRO 72K',
        'SAFENET_PRO_ANYWHERE'       => 'SafeNet eToken PRO Anywhere',
        'SAFENET_IKEY_4000'          => 'SafeNet iKey 4000',
        'EPASS_3003'                 => 'ePass 3003',
        'OTHER_FIPS_140_2_LEVEL_2'   => 'Another FIPS 140-2 Level 2 token',
    ];

    /** the only shipping the gateway offers for these products */
    const DEFAULT_SHIPPING = 'STANDARD';

    // contact fields the gateway takes
    const ORGANIZATION_FIELDS   = ['name', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1', 'address_line_2'];
    const ADMINISTRATOR_FIELDS  = ['first_name', 'last_name', 'title', 'email', 'phone'];
    const ORGANIZATION_REQUIRED = ['name', 'phone', 'country', 'region', 'city', 'postal_code', 'address_line_1'];
    const ADMINISTRATOR_REQUIRED = ['first_name', 'last_name', 'email', 'phone'];

    public static function configurationTemplate()
    {
        return EmailTemplates::DS_CONFIGURATION_TEMPLATE;
    }

    public static function isDocumentSigningWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isDocumentSigningService($serviceId)
    {
        return static::isService($serviceId);
    }

    /**
     * Gateway products the module can sell: the four TheSSLStore document signing products. GoGetSSL lists document
     * signing under "tls" and does not sell it through this flow (ProductCatalog::NOT_SSL_NAMES keeps it out).
     */
    public static function isSupportedRemoteProduct($vendor, $category, $remoteId = '')
    {
        return strtolower((string)$category) === self::CATEGORY
            && strtoupper((string)$vendor) === 'TSS'
            && in_array((string)$remoteId, self::SUPPORTED_PRODUCTS, true);
    }

    /**
     * Delivery methods the gateway product offers, in the order the client sees them
     */
    public static function methods(array $rawData)
    {
        $methods = isset($rawData['provisioning']['methods']) && is_array($rawData['provisioning']['methods']) ? $rawData['provisioning']['methods'] : self::METHODS;

        return array_values(array_intersect(self::METHODS, array_map('strval', $methods)));
    }

    /**
     * Shipping options of the product: [['id' => 'STANDARD', 'label' => ..], ...]. Both the token and its shipping are
     * free for these products, so no price is shown.
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
                $id           = strtoupper((string)$option['id']);
                $options[$id] = ['id' => $id, 'label' => !empty($option['label']) ? (string)$option['label'] : ucfirst(strtolower($id)) . ' Shipping'];
            }
        }
        if(!$options)
        {
            $options[self::DEFAULT_SHIPPING] = ['id' => self::DEFAULT_SHIPPING, 'label' => 'Standard Shipping'];
        }

        return array_values($options);
    }

    /**
     * What the order form offers for a service: ['methods' => [...], 'shipping_options' => [...], 'models' => [...],
     * 'term' => int, 'renewal' => bool]
     */
    public static function availableDelivery($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        $remote    = static::remoteProduct($packageId);
        $rawData   = $remote && is_array($remote->rawData) ? $remote->rawData : [];

        return [
            'methods'          => static::methods($rawData),
            'shipping_options' => static::shippingOptions($rawData),
            'models'           => self::TOKEN_MODELS,
            'term'             => static::termForBillingCycle((string)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('billingcycle'), static::availableTerms($packageId)),
            'renewal'          => !empty(static::stored($serviceId)['previous']),
        ];
    }

    /**
     * Builds and validates the provisioning part of the order.
     *
     * @param array  $input   method, shipping, model
     * @param array  $contact shipping contact (optional: the organization address is used when it is empty)
     * @return array
     */
    public static function provisioning(array $input, array $contact = [])
    {
        $method = (string)($input['method'] ?? '');
        if(!in_array($method, self::METHODS, true))
        {
            throw new \Exception('dsMethodRequired');
        }

        if($method === self::METHOD_EXISTING_TOKEN)
        {
            $model = strtoupper(trim((string)($input['model'] ?? '')));
            if(!isset(self::TOKEN_MODELS[$model]))
            {
                throw new \Exception('dsModelRequired');
            }

            return ['method' => $method, 'model' => $model];
        }

        $shipping = strtoupper(trim((string)($input['shipping'] ?? ''))) ?: self::DEFAULT_SHIPPING;
        $result   = ['method' => $method, 'shipping' => ['id' => $shipping]];
        $contact  = array_filter(array_map('trim', array_map('strval', $contact)));
        if($contact)
        {
            $result['shipping']['contact'] = $contact;
        }

        return $result;
    }

    /**
     * Organization and administrator of the order, with the fields this product type needs. The administrator takes
     * no address here (unlike S/MIME): the gateway asks only for the name, title, e-mail and phone.
     *
     * @param array $missing filled with ['org'|'admin', field] when a required field is empty
     */
    public static function contacts(array $organization, array $administrator, &$missing = null)
    {
        $missing = null;
        $org     = [];
        foreach(self::ORGANIZATION_FIELDS as $field)
        {
            $value       = trim((string)($organization[$field] ?? ''));
            $org[$field] = $value !== '' ? $value : null;
        }
        $admin = [];
        foreach(self::ADMINISTRATOR_FIELDS as $field)
        {
            $value         = trim((string)($administrator[$field] ?? ''));
            $admin[$field] = $value !== '' ? $value : null;
        }

        foreach(self::ORGANIZATION_REQUIRED as $field)
        {
            if($org[$field] === null)
            {
                $missing = ['org', $field];
                break;
            }
        }
        if(!$missing)
        {
            foreach(self::ADMINISTRATOR_REQUIRED as $field)
            {
                if($admin[$field] === null)
                {
                    $missing = ['admin', $field];
                    break;
                }
            }
        }
        $org['country'] = $org['country'] !== null ? strtoupper($org['country']) : null;

        return ['organization' => $org, 'administrator' => $admin];
    }

    /**
     * Places the order. $order: provisioning (see provisioning()) and contacts {organization, administrator}.
     */
    public static function create($serviceId, array $order)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::termForBillingCycle($service->billingcycle, static::availableTerms($service->packageid));
        $payload   = [
            'product'      => ['id' => SSLTrustCenterApi::productId($productId), 'term' => $term],
            'provisioning' => $order['provisioning'],
            'contacts'     => $order['contacts'],
        ];

        // one order per service: a second submit (double click, two tabs) must not order again
        $locked = Capsule::table('tblsslorders')->where('id', $ssl->id)->where('status', 'Awaiting Configuration')->update(['status' => 'Configuration Submitted']);
        if(!$locked)
        {
            throw new \Exception('The ' . static::LABEL . ' certificate has already been ordered');
        }
        try
        {
            $response = CertificateService::api($serviceId)->createDocumentSigning($payload);
        }
        catch(\Exception $e)
        {
            // not ordered: the client can correct the data and submit again
            Capsule::table('tblsslorders')->where('id', $ssl->id)->update(['status' => 'Awaiting Configuration']);
            throw $e;
        }

        $provisioning = $order['provisioning'];
        $organization = (string)$order['contacts']['organization']['name'];

        return static::saveCreated($serviceId, $ssl, [
            'request'      => $payload,
            'term'         => $term,
            'method'       => $provisioning['method'],
            'tokenModel'   => $provisioning['model'] ?? '',
            'shipping'     => $provisioning['shipping']['id'] ?? '',
            'organization' => $organization,
        ], $response, $organization, static::LABEL . ' certificate ordered for ' . $organization . ' (' . static::describeDelivery($provisioning) . ')');
    }

    /** "a new token, standard shipping" / "the client's own token (SafeNet eToken 5110)" */
    public static function describeDelivery(array $provisioning)
    {
        if(($provisioning['method'] ?? '') === self::METHOD_EXISTING_TOKEN)
        {
            $model = strtoupper((string)($provisioning['model'] ?? ''));

            return 'the client\'s own token' . (isset(self::TOKEN_MODELS[$model]) ? ' (' . self::TOKEN_MODELS[$model] . ')' : '');
        }

        return 'a new token, ' . strtolower((string)($provisioning['shipping']['id'] ?? self::DEFAULT_SHIPPING)) . ' shipping';
    }

    /**
     * A renewal can reuse the details: the token the client has stays, and a shipped token is renewed onto that token.
     */
    public static function canRenewWithSameDetails(array $stored)
    {
        return !empty($stored['request']['provisioning']['method'] ?? '');
    }

    protected static function renewWithSameDetails($serviceId, array $before)
    {
        if(!static::canRenewWithSameDetails($before))
        {
            return false;
        }
        $request = (array)$before['request'];
        static::create($serviceId, ['provisioning' => (array)$request['provisioning'], 'contacts' => (array)$request['contacts']]);

        return true;
    }

    /** the vendor's token set-up page, shown to the client while the token is not set up yet */
    protected static function applyItem(array $stored, array $item)
    {
        foreach(['token_setup_link' => 'tokenSetupLink', 'enrollment_link' => 'enrollmentLink'] as $from => $to)
        {
            if(!empty($item[$from]))
            {
                $stored[$to] = (string)$item[$from];
            }
        }
        if(!empty($item['organization']['name']))
        {
            $stored['organization'] = (string)$item['organization']['name'];
        }

        return $stored;
    }

    /**
     * "Document Signing Certificate Issued": how the client gets the certificate onto the token
     */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
        if(($stored['status'] ?? '') !== 'active')
        {
            return;
        }

        EmailTemplates::sendEmail(EmailTemplates::DS_ISSUED_TEMPLATE, $serviceId, [
            'ds_organization' => (string)($stored['organization'] ?? ''),
            'ds_valid_till'   => (string)($stored['validTill'] ?? ''),
            'ds_method'       => (string)($stored['method'] ?? ''),
        ]);
    }
}
