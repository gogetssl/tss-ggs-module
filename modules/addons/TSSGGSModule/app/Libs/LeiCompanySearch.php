<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Company search of the LEI order form: fills in the legal entity from a public register.
 *
 *  - OpenCorporates (https://api.opencorporates.com/v0.4): company registers of most countries. Needs an API token
 *    (Settings > General > LEI codes); without one the search uses GLEIF only.
 *  - GLEIF (https://api.gleif.org/api/v1, free, no token): every entity that has, or had, an LEI. Its results also tell
 *    the client that the entity already has an LEI (its status: ISSUED, LAPSED, ...).
 *
 * Results: [['source', 'legal_name', 'registration_number', 'jurisdiction', 'incorporation_date', 'address' => [..],
 * 'lei', 'lei_status'], ...]. Never throws: a register that does not answer just gives no results.
 */
class LeiCompanySearch
{
    const OPEN_CORPORATES = 'https://api.opencorporates.com/v0.4';
    const GLEIF           = 'https://api.gleif.org/api/v1';
    const LIMIT           = 10;
    const TIMEOUT         = 8;

    /** the OpenCorporates API token (stored encrypted), '' when none */
    public static function token()
    {
        $stored = (string)GeneralSettings::get('openCorporatesToken');
        if($stored === '')
        {
            return '';
        }
        $token = (string)\decrypt($stored);

        return $token !== '' ? $token : '';
    }

    /**
     * @param string $query   company name or registration number
     * @param string $country ISO 3166-1 alpha-2 code to search in ('' = everywhere)
     */
    public static function search($query, $country = '')
    {
        $query   = trim(preg_replace('/\s+/u', ' ', (string)$query));
        $country = strtoupper(trim((string)$country));
        if(mb_strlen($query) < 2)
        {
            return [];
        }
        $country = preg_match('/^[A-Z]{2}$/', $country) ? $country : '';

        $results = self::token() !== '' ? self::openCorporates($query, $country) : [];
        $gleif   = self::gleif($query, $country);

        // an OpenCorporates company that has an LEI: GLEIF tells its number and status
        foreach($results as &$result)
        {
            foreach($gleif as $key => $entity)
            {
                if(self::sameEntity($result, $entity))
                {
                    $result['lei']        = $entity['lei'];
                    $result['lei_status'] = $entity['lei_status'];
                    unset($gleif[$key]);
                    break;
                }
            }
        }
        unset($result);

        return array_slice(array_merge($results, array_values($gleif)), 0, self::LIMIT * 2);
    }

    protected static function sameEntity(array $a, array $b)
    {
        $number = function($value) {
            return strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string)$value));
        };

        return $number($a['registration_number']) !== '' && $number($a['registration_number']) === $number($b['registration_number'])
            && substr($a['jurisdiction'], 0, 2) === substr($b['jurisdiction'], 0, 2);
    }

    protected static function openCorporates($query, $country)
    {
        $params = ['q' => $query, 'per_page' => self::LIMIT, 'api_token' => self::token()];
        if($country !== '')
        {
            $params['country_code'] = strtolower($country);
        }
        $data    = self::get(self::OPEN_CORPORATES . '/companies/search?' . http_build_query($params));
        $results = [];
        foreach((array)($data['results']['companies'] ?? []) as $row)
        {
            $company = (array)($row['company'] ?? []);
            if(empty($company['name']))
            {
                continue;
            }
            $jurisdiction = self::jurisdiction((string)($company['jurisdiction_code'] ?? ''));
            $address      = (array)($company['registered_address'] ?? []);
            $results[]    = [
                'source'              => 'opencorporates',
                'legal_name'          => self::plain((string)$company['name']),
                'registration_number' => (string)($company['company_number'] ?? ''),
                'jurisdiction'        => $jurisdiction,
                'incorporation_date'  => (string)($company['incorporation_date'] ?? ''),
                'status'              => (string)($company['current_status'] ?? ''),
                'address'             => [
                    'address_line_1' => self::plain((string)($address['street_address'] ?? ($company['registered_address_in_full'] ?? ''))),
                    'city'           => self::plain((string)($address['locality'] ?? '')),
                    'region'         => self::plain((string)($address['region'] ?? '')),
                    'postal_code'    => self::plain((string)($address['postal_code'] ?? '')),
                    'country'        => substr($jurisdiction, 0, 2),
                ],
                'lei'        => '',
                'lei_status' => '',
            ];
        }

        return $results;
    }

    protected static function gleif($query, $country)
    {
        $params = ['filter[fulltext]' => $query, 'page[size]' => self::LIMIT];
        if($country !== '')
        {
            $params['filter[entity.legalAddress.country]'] = $country;
        }
        $data    = self::get(self::GLEIF . '/lei-records?' . http_build_query($params));
        $results = [];
        foreach((array)($data['data'] ?? []) as $record)
        {
            $entity  = (array)($record['attributes']['entity'] ?? []);
            $address = (array)($entity['legalAddress'] ?? []);
            if(empty($entity['legalName']['name']))
            {
                continue;
            }
            $results[] = [
                'source'              => 'gleif',
                'legal_name'          => self::plain((string)$entity['legalName']['name']),
                'registration_number' => (string)($entity['registeredAs'] ?? ''),
                'jurisdiction'        => strtoupper((string)($entity['jurisdiction'] ?? ($address['country'] ?? ''))),
                'incorporation_date'  => substr((string)($entity['creationDate'] ?? ''), 0, 10),
                'status'              => (string)($entity['status'] ?? ''),
                'address'             => [
                    'address_line_1' => self::plain((string)(((array)($address['addressLines'] ?? []))[0] ?? '')),
                    'city'           => self::plain((string)($address['city'] ?? '')),
                    // GLEIF regions are ISO 3166-2 codes (LV-RIX): the form takes a name, so only a plain value is kept
                    'region'         => preg_match('/^[A-Z]{2}-/', (string)($address['region'] ?? '')) ? '' : (string)($address['region'] ?? ''),
                    'postal_code'    => self::plain((string)($address['postalCode'] ?? '')),
                    'country'        => strtoupper((string)($address['country'] ?? '')),
                ],
                'lei'        => (string)($record['attributes']['lei'] ?? ($record['id'] ?? '')),
                'lei_status' => (string)($record['attributes']['registration']['status'] ?? ''),
            ];
        }

        return $results;
    }

    /**
     * A register value without the characters the gateway refuses in names and addresses (LeiService::FORBIDDEN_*):
     * '"envers one" SIA' -> 'envers one SIA'
     */
    public static function plain($value)
    {
        $value = preg_replace([LeiService::FORBIDDEN_TEXT, LeiService::FORBIDDEN_NAME], ' ', (string)$value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    /** OpenCorporates jurisdiction code to ISO 3166: lv -> LV, us_ny -> US-NY */
    public static function jurisdiction($code)
    {
        $parts = explode('_', strtolower(trim((string)$code)), 2);
        if(!preg_match('/^[a-z]{2}$/', $parts[0]))
        {
            return '';
        }

        return strtoupper($parts[0]) . (isset($parts[1]) && $parts[1] !== '' ? '-' . strtoupper($parts[1]) : '');
    }

    protected static function get($url)
    {
        try
        {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => self::TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER     => ['Accept: application/json, application/vnd.api+json'],
                CURLOPT_USERAGENT      => 'WHMCS LEI order form',
            ]);
            $body = curl_exec($curl);
            $code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if($body === false || $code < 200 || $code >= 300)
            {
                return [];
            }
            $data = json_decode((string)$body, true);

            return is_array($data) ? $data : [];
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }
}
