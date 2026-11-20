<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;

/**
 * LEI codes (Legal Entity Identifier), ordered through the gateway (/lei).
 *
 * An LEI is registered with the LEI issuer for the legal entity the client enters after paying (name, registration
 * number, jurisdiction, addresses) and the person signing the application. The issuer checks the data (processing),
 * may ask the client to confirm what it found (incomplete: the issuer emails the client), finishes (pending) and
 * issues the 20-character code (active, with valid_from / valid_till).
 *
 * Renewal: from 60 days before valid_till (or after it), POST /lei/{id}/renew creates a new LEI record (its own id,
 * is_renewal true) that replaces the old one once the issuer confirms - the service follows the new record.
 *
 * Vendors: GoGetSSL only for now (161 LEI Code, 162 LEI Code PRO). TheSSLStore is planned: add its products to PRODUCTS.
 * 165 (Renewal Transfer of an LEI issued elsewhere) is not sold (Vlad, 2026-11-20).
 *
 * Stored order (see GatewayOrderService) plus: leiNumber, legalName, legalEntity, signingAuthority, registeredLegalAddress,
 * registeredHeadquartersAddress, isRenewal. tblhosting.domain = the legal name.
 */
class LeiService extends GatewayOrderService
{
    const CATEGORY     = 'lei';
    const REQUEST_NAME = 'lei';
    const LABEL        = 'LEI';

    /** gateway products the module sells, per vendor */
    const PRODUCTS = ['GGS' => ['161', '162']];

    /** terms the gateway sells (months) */
    const TERMS = [12, 24, 36, 48, 60];

    /** the gateway's text rules: no field may hold these, names and addresses not the second set either */
    const FORBIDDEN_TEXT = '/[<>{}%*;=\x00-\x1F\x7F]/u';
    const FORBIDDEN_NAME = '/[()\[\]|&@:"\/]/u';

    /** field => max length */
    const ENTITY_LENGTHS  = ['legal_name' => 150, 'registration_authority_entity_id' => 50];
    const ADDRESS_LENGTHS = ['address_line_1' => 100, 'city' => 50, 'region' => 50, 'postal_code' => 50];
    const ADDRESS_REQUIRED = ['address_line_1', 'city', 'postal_code', 'country'];
    const NAME_LENGTH      = 50;

    public static function configurationTemplate()
    {
        return EmailTemplates::LEI_CONFIGURATION_TEMPLATE;
    }

    public static function isLeiWhmcsProduct($whmcsProductId)
    {
        return static::isWhmcsProduct($whmcsProductId);
    }

    public static function isLeiService($serviceId)
    {
        return static::isService($serviceId);
    }

    public static function isSupportedRemoteProduct($vendor, $category, $remoteId = '')
    {
        return strtolower((string)$category) === self::CATEGORY
            && in_array((string)$remoteId, self::PRODUCTS[strtoupper((string)$vendor)] ?? [], true);
    }

    // ------------------------------------------------------------------ order data

    /**
     * The legal entity part of the order, checked against the gateway's rules.
     *
     * @param array $input   legal_name, registration_authority_entity_id, legal_jurisdiction, incorporation_date,
     *                       legal_address{}, hq_differs, headquarters_address{}, level2_available / _consolidated / _ultimate
     * @param array $error   filled with [language key, field] when the data is refused
     * @return array|null
     */
    public static function legalEntity(array $input, &$error = null)
    {
        $error  = null;
        $entity = [];
        foreach(self::ENTITY_LENGTHS as $field => $length)
        {
            $value = self::clean($input[$field] ?? '');
            if($value === '')
            {
                $error = ['leiFieldRequired', $field];

                return null;
            }
            if(!self::allowed($value, $field === 'legal_name') || mb_strlen($value) > $length)
            {
                $error = ['leiFieldInvalid', $field];

                return null;
            }
            $entity[$field] = $value;
        }

        $jurisdiction = strtoupper(self::clean($input['legal_jurisdiction'] ?? ''));
        if(!preg_match('/^[A-Z]{2}(-[A-Z0-9]{1,3})?$/', $jurisdiction))
        {
            $error = ['leiFieldInvalid', 'legal_jurisdiction'];

            return null;
        }
        $entity['legal_jurisdiction'] = $jurisdiction;

        $date = self::clean($input['incorporation_date'] ?? '');
        if($date !== '')
        {
            $parsed = \DateTime::createFromFormat('!Y-m-d', $date);
            if(!$parsed || $parsed->format('Y-m-d') !== $date || $parsed > new \DateTime())
            {
                $error = ['leiFieldInvalid', 'incorporation_date'];

                return null;
            }
            $entity['incorporation_date'] = $date;
        }

        $legal = self::address((array)($input['legal_address'] ?? []), 'legal', $error);
        if($legal === null)
        {
            return null;
        }
        $entity['legal_address']        = $legal;
        $entity['headquarters_address'] = null;
        if(!empty($input['hq_differs']))
        {
            $headquarters = self::address((array)($input['headquarters_address'] ?? []), 'hq', $error);
            if($headquarters === null)
            {
                return null;
            }
            $entity['headquarters_address'] = $headquarters;
        }

        $entity['level2_data'] = [
            'available'    => !empty($input['level2_available']),
            'consolidated' => !empty($input['level2_available']) && !empty($input['level2_consolidated']),
            'ultimate'     => !empty($input['level2_available']) && !empty($input['level2_ultimate']),
        ];

        return $entity;
    }

    /** an address in the gateway format, or null with $error = [key, '<prefix>_<field>'] */
    protected static function address(array $input, $prefix, &$error)
    {
        $address = [];
        foreach(['address_line_1', 'city', 'region', 'postal_code', 'country'] as $field)
        {
            $value = self::clean($input[$field] ?? '');
            if($field === 'country')
            {
                $value = strtoupper($value);
            }
            if($value === '')
            {
                if(in_array($field, self::ADDRESS_REQUIRED, true))
                {
                    $error = ['leiFieldRequired', $prefix . '_' . $field];

                    return null;
                }
                $address[$field] = null;
                continue;
            }
            $invalid = $field === 'country'
                ? !preg_match('/^[A-Z]{2}$/', $value)
                : (!self::allowed($value, true) || mb_strlen($value) > self::ADDRESS_LENGTHS[$field]);
            if($invalid)
            {
                $error = ['leiFieldInvalid', $prefix . '_' . $field];

                return null;
            }
            $address[$field] = $value;
        }

        return $address;
    }

    /** the person signing the application: ['first_name' => .., 'last_name' => ..] or null with $error */
    public static function signingAuthority(array $input, &$error = null)
    {
        $error  = null;
        $person = [];
        foreach(['first_name', 'last_name'] as $field)
        {
            $value = self::clean($input[$field] ?? '');
            if($value === '')
            {
                $error = ['leiFieldRequired', 'signing_' . $field];

                return null;
            }
            if(!self::allowed($value, true) || mb_strlen($value) > self::NAME_LENGTH)
            {
                $error = ['leiFieldInvalid', 'signing_' . $field];

                return null;
            }
            $person[$field] = $value;
        }

        return $person;
    }

    protected static function clean($value)
    {
        return trim(preg_replace('/\s+/u', ' ', (string)$value));
    }

    protected static function allowed($value, $name)
    {
        return !preg_match(self::FORBIDDEN_TEXT, $value) && (!$name || !preg_match(self::FORBIDDEN_NAME, $value));
    }

    // ------------------------------------------------------------------ orders

    /**
     * What the order page needs: term, renewal (the issued LEI the next order renews) and the data of the last order
     */
    public static function orderContext($serviceId)
    {
        $packageId = (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid');
        $stored    = static::stored($serviceId);
        $previous  = (array)($stored['previous'] ?? []);
        $last      = $previous ? (array)end($previous) : [];

        return [
            'term'    => static::term($serviceId, $packageId),
            // an issued LEI is renewed (POST /lei/{id}/renew); a rejected / cancelled one is ordered again
            'renewal' => !empty($last['itemId']) && !empty($last['leiNumber']) ? $last : null,
            'last'    => $last,
        ];
    }

    protected static function term($serviceId, $packageId)
    {
        $cycle = AddonCertificates::billingCycleOf((int)$serviceId) ?? (string)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('billingcycle');

        return static::termForBillingCycle($cycle, static::availableTerms($packageId) ?: self::TERMS);
    }

    /**
     * Places a new LEI order (first order, or after a rejected / cancelled one)
     */
    public static function create($serviceId, array $legalEntity, array $signingAuthority)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);

        $productId = (string)Capsule::table('tblproducts')->where('id', (int)$service->packageid)->value('configoption2');
        $term      = static::term($serviceId, $service->packageid);
        $payload   = [
            'product'           => ['id' => SSLTrustCenterApi::productId($productId), 'term' => $term],
            'legal_entity'      => $legalEntity,
            'signing_authority' => $signingAuthority,
        ];

        $response = static::placeLocked($serviceId, $ssl, function() use ($serviceId, $payload) {
            return CertificateService::api($serviceId)->createLei($payload);
        });

        return static::saveCreated($serviceId, $ssl, [
            'request'          => $payload,
            'term'             => $term,
            'legalName'        => $legalEntity['legal_name'],
            'legalEntity'      => $legalEntity,
            'signingAuthority' => $signingAuthority,
        ], $response, $legalEntity['legal_name'], 'LEI ordered for ' . $legalEntity['legal_name']);
    }

    /**
     * Renews the issued LEI of the last order: a new order and a new LEI record that replaces it once issued
     *
     * @param bool       $level1Same       the legal entity data is unchanged (false: the issuer checks it again)
     * @param array|null $signingAuthority null: the signing authority of the LEI
     */
    public static function renew($serviceId, $level1Same, array $signingAuthority = null)
    {
        [$service, $ssl] = static::requireOrderable($serviceId);
        $renewal = static::orderContext($serviceId)['renewal'];
        if(!$renewal)
        {
            throw new \Exception('There is no issued LEI to renew');
        }

        $term    = static::term($serviceId, $service->packageid);
        $payload = ['level1_data_same' => (bool)$level1Same, 'term' => $term];
        if($signingAuthority)
        {
            $payload['signing_authority'] = $signingAuthority;
        }

        $response = static::placeLocked($serviceId, $ssl, function() use ($serviceId, $renewal, $payload) {
            return CertificateService::api($serviceId)->renewLei((int)$renewal['itemId'], $payload);
        });

        $legalName = (string)($renewal['legalName'] ?? '');

        return static::saveCreated($serviceId, $ssl, [
            'request'          => $payload + ['renews' => (int)$renewal['itemId']],
            'term'             => $term,
            'legalName'        => $legalName,
            'legalEntity'      => (array)($renewal['legalEntity'] ?? []),
            'signingAuthority' => $signingAuthority ?: (array)($renewal['signingAuthority'] ?? []),
        ], $response, $legalName, 'LEI ' . ($renewal['leiNumber'] ?? '') . ' renewal ordered' . ($level1Same ? '' : ' (legal entity data changed)'));
    }

    /** one order per service: a second submit (double click, two tabs) must not order again */
    protected static function placeLocked($serviceId, $ssl, callable $call)
    {
        $locked = Capsule::table('tblsslorders')->where('id', $ssl->id)->where('status', 'Awaiting Configuration')->update(['status' => 'Configuration Submitted']);
        if(!$locked)
        {
            throw new \Exception('The LEI has already been ordered');
        }
        try
        {
            return $call();
        }
        catch(\Exception $e)
        {
            // not ordered: the client can correct the data and submit again
            Capsule::table('tblsslorders')->where('id', $ssl->id)->update(['status' => 'Awaiting Configuration']);
            throw $e;
        }
    }

    /**
     * Renewal with the same details: the LEI is renewed with unchanged legal entity data and signing authority
     */
    public static function canRenewWithSameDetails(array $stored)
    {
        return !empty($stored['itemId']) && !empty($stored['leiNumber']);
    }

    protected static function renewWithSameDetails($serviceId, array $before)
    {
        if(!static::canRenewWithSameDetails($before))
        {
            return false;
        }
        static::renew($serviceId, true);

        return true;
    }

    /** LEI orders are cancelled as orders (POST /orders/{order id}/cancel) */
    public static function cancel($serviceId, $reason)
    {
        $stored = static::stored($serviceId);
        if(empty($stored['orderId']) || empty($stored['itemId']) || in_array($stored['status'] ?? '', array_diff(static::FINAL_STATUSES, ['active']), true))
        {
            return false;
        }

        CertificateService::api($serviceId)->cancelOrder((int)$stored['orderId'], $reason);

        $stored['status'] = 'cancelled';
        static::save($serviceId, $stored);
        Capsule::table('tblsslorders')->where('serviceid', (int)$serviceId)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    // ------------------------------------------------------------------ hooks

    protected static function applyItem(array $stored, array $item)
    {
        if(!empty($item['lei_number']))
        {
            $stored['leiNumber'] = strtoupper((string)$item['lei_number']);
        }
        foreach(['valid_from' => 'validFrom', 'valid_till' => 'validTill'] as $from => $to)
        {
            if(!empty($item[$from]))
            {
                $stored[$to] = (string)$item[$from];
            }
        }
        if(!empty($item['legal_entity']['legal_name']))
        {
            $stored['legalName']   = (string)$item['legal_entity']['legal_name'];
            $stored['legalEntity'] = (array)$item['legal_entity'];
        }
        if(!empty($item['signing_authority']) && is_array($item['signing_authority']))
        {
            $stored['signingAuthority'] = $item['signing_authority'];
        }
        foreach(['registered_legal_address' => 'registeredLegalAddress', 'registered_headquarters_address' => 'registeredHeadquartersAddress'] as $from => $to)
        {
            if(!empty($item[$from]) && is_array($item[$from]))
            {
                $stored[$to] = $item[$from];
            }
        }
        if(isset($item['is_renewal']))
        {
            $stored['isRenewal'] = (bool)$item['is_renewal'];
        }

        return $stored;
    }

    protected static function previousEntry(array $stored)
    {
        return [
            'leiNumber'        => $stored['leiNumber'] ?? '',
            'legalName'        => $stored['legalName'] ?? '',
            'legalEntity'      => $stored['legalEntity'] ?? [],
            'signingAuthority' => $stored['signingAuthority'] ?? [],
        ];
    }

    /** "LEI Issued" e-mail (also after a renewal: the registration period moved) */
    protected static function onStatusChange($serviceId, $previousStatus, array $stored)
    {
        if(($stored['status'] ?? '') !== 'active')
        {
            return;
        }

        EmailTemplates::sendEmail(EmailTemplates::LEI_ISSUED_TEMPLATE, $serviceId, [
            'lei_number'     => (string)($stored['leiNumber'] ?? ''),
            'lei_legal_name' => (string)($stored['legalName'] ?? ''),
            'lei_valid_till' => (string)($stored['validTill'] ?? ''),
            'lei_is_renewal' => !empty($stored['isRenewal']) ? 1 : 0,
        ]);
    }

    /** the GLEIF page of an LEI */
    public static function gleifUrl($leiNumber)
    {
        return 'https://search.gleif.org/#/record/' . rawurlencode(strtoupper((string)$leiNumber));
    }
}
