<?php

namespace TSSGGSModule\App\Libs\AutoInstall\Concerns;

use TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Models\Request;
use TSSGGSModule\App\Libs\AutoInstall\Cpanel;

/**
 * Domain validation done by AutoInstall: DNS records, HTTP files, the switch to e-mail validation.
 * Part of AutoInstallHelpers (used there as a trait).
 */
trait AutoInstallValidation
{
    /**
     * Creates the DNS validation records (CNAME / TXT) in the client's cPanel DNS zone.
     * Works when cPanel is authoritative for the domain - otherwise the CA will not see the record and the
     * validation is switched to email automatically (wildcard DNS timeout setting / DNS_MAX_VALIDATE_ATTEMPTS).
     */
    protected static function createDnsRecords($task, array $taskData, array $domains)
    {
        if(!empty($taskData['dns_switched_to_email']))
        {
            return $taskData;
        }

        $serviceId = (int)$task->service_id;
        $clientId  = (int)$task->client_id;
        $created   = isset($taskData['dns_records_created']) ? (array)$taskData['dns_records_created'] : [];
        $failures  = isset($taskData['dns_record_failures']) ? (array)$taskData['dns_record_failures'] : [];

        foreach($domains as $domain)
        {
            if(!isset($domain['method'], $domain['name']) || strtolower($domain['method']) !== 'dns')
            {
                continue;
            }

            $name = $domain['name'];
            if(in_array($name, $created) || (isset($failures[$name]) && $failures[$name] >= self::MAX_UPLOAD_ATTEMPTS))
            {
                continue;
            }

            try
            {
                $record = self::parseDnsValidation($domain);
                if(!$record)
                {
                    throw new \Exception('Unsupported DNS validation data format: ' .
                        json_encode(isset($domain['validation']) ? $domain['validation'] : null));
                }

                // *.example.com / www.example.com are not separate cPanel domains - look up the base domain
                $lookupDomain  = strtolower(preg_replace('/^\*\./', '', $name));
                $cpanelService = Cpanel::getServiceByDomain($clientId, $lookupDomain);
                if($cpanelService === false && strpos($lookupDomain, 'www.') === 0)
                {
                    $lookupDomain  = substr($lookupDomain, 4);
                    $cpanelService = Cpanel::getServiceByDomain($clientId, $lookupDomain);
                }
                if($cpanelService === false)
                {
                    throw new \Exception('cPanel account not found for domain: ' . $name);
                }

                $cpanel = new Cpanel();
                $cpanel->setService($cpanelService);

                $zone = $cpanel->getZoneForDomain($cpanelService->user, $lookupDomain);
                if(!$zone)
                {
                    throw new \Exception('DNS zone not found in cPanel for domain: ' . $lookupDomain);
                }

                if($cpanel->zoneRecordExists($cpanelService->user, $zone, $record['name'], $record['type'], $record['value']))
                {
                    self::log($clientId, $serviceId, 'info', '[Validate] DNS record already exists in zone ' . $zone . ': ' .
                        $record['name'] . ' ' . $record['type'] . ' ' . $record['value']);
                }
                else
                {
                    $cpanel->addZoneRecord($cpanelService->user, $zone, $record['name'], $record['type'], $record['value'], self::DNS_RECORD_TTL);
                    self::log($clientId, $serviceId, 'success', '[Validate] DNS record created in cPanel zone ' . $zone . ': ' .
                        $record['name'] . ' ' . $record['type'] . ' ' . $record['value']);
                }

                $created[] = $name;
            }
            catch(\Exception $e)
            {
                $failures[$name] = (isset($failures[$name]) ? (int)$failures[$name] : 0) + 1;
                self::log($clientId, $serviceId, 'error', '[Validate] Failed to create DNS record for domain: ' . $name . ' - ' . $e->getMessage() .
                    ' | The record has to be added manually, otherwise the validation will be switched to email.');
            }
        }

        $taskData['dns_records_created'] = $created;
        $taskData['dns_record_failures'] = $failures;

        return $taskData;
    }

    /**
     * DNS validation record from the order domain entry: ['name' => FQDN with trailing dot, 'type' => CNAME|TXT, 'value' => ...].
     * Accepts separate fields (name/host + type + value/target) or a zone-file style string in "value"
     * ("_hash.example.com. [ttl] [IN] CNAME target." / "... TXT \"value\"").
     */
    public static function parseDnsValidation(array $domain)
    {
        $validation = isset($domain['validation']) && is_array($domain['validation']) ? $domain['validation'] : [];
        $baseDomain = strtolower(preg_replace('/^\*\./', '', (string)$domain['name']));

        $name  = '';
        $type  = '';
        $value = '';

        foreach(['name', 'host', 'record_name'] as $key)
        {
            if(!empty($validation[$key]) && is_string($validation[$key]))
            {
                $name = $validation[$key];
                break;
            }
        }
        foreach(['type', 'record_type'] as $key)
        {
            if(!empty($validation[$key]) && is_string($validation[$key]))
            {
                $type = strtoupper($validation[$key]);
                break;
            }
        }
        foreach(['target', 'points_to', 'record_value'] as $key)
        {
            if(!empty($validation[$key]) && is_string($validation[$key]))
            {
                $value = $validation[$key];
                break;
            }
        }

        $raw = isset($validation['value']) ? $validation['value'] : (isset($validation['record']) ? $validation['record'] : '');
        if(is_array($raw))
        {
            $raw = implode(' ', $raw);
        }
        $raw = trim((string)$raw);

        if($name && $value)
        {
            $type = $type ?: 'CNAME';
        }
        elseif($raw !== '' && preg_match('/^\s*(\S+)\s+(?:\d+\s+)?(?:IN\s+)?(CNAME|TXT)\s+(.+?)\s*$/i', $raw, $m))
        {
            $name  = $m[1];
            $type  = strtoupper($m[2]);
            $value = $m[3];
        }
        elseif($name && $raw !== '')
        {
            $value = $raw;
            $type  = $type ?: 'CNAME';
        }
        else
        {
            return null;
        }

        if(!in_array($type, ['CNAME', 'TXT']))
        {
            return null;
        }

        $value = trim($value);
        if($type === 'TXT')
        {
            $value = trim($value, '"');
        }

        // Relative name (e.g. "_hash") - append the validated domain
        $name = rtrim(trim($name), '.');
        if(strcasecmp($name, $baseDomain) !== 0 && strtolower(substr($name, -strlen('.' . $baseDomain))) !== '.' . $baseDomain)
        {
            $name .= '.' . $baseDomain;
        }

        return ['name' => $name . '.', 'type' => $type, 'value' => $value];
    }

    protected static function switchDnsToEmailIfNeeded($task, array $taskData, SSLTrustCenterApi $api, $apiOrderId, array $order, array $domains)
    {
        if(!empty($taskData['dns_switched_to_email']))
        {
            return $taskData;
        }

        $dnsDomains = [];
        foreach($domains as $domain)
        {
            if(isset($domain['method'], $domain['name']) && strtolower($domain['method']) === 'dns')
            {
                $dnsDomains[] = $domain['name'];
            }
        }
        if(empty($dnsDomains))
        {
            return $taskData;
        }

        $serviceId = (int)$task->service_id;
        $clientId  = (int)$task->client_id;
        $reason    = '';

        // Wildcard: time-based timeout (dns_check_start is set at task creation)
        if(!empty($taskData['is_wildcard']))
        {
            $start          = !empty($taskData['dns_check_start']) ? (int)$taskData['dns_check_start'] : strtotime($task->created_at);
            $elapsedMinutes = (int)round((time() - $start) / 60);
            $timeoutMinutes = self::getWildcardDnsTimeoutMinutes();
            self::log($clientId, $serviceId, 'info', '[Validate] Wildcard DNS check - elapsed ' . $elapsedMinutes . ' / ' . $timeoutMinutes . ' minutes');
            if($elapsedMinutes >= $timeoutMinutes)
            {
                $reason = 'Wildcard DNS timeout reached (' . $elapsedMinutes . ' min)';
            }
        }

        // All DNS orders: max validation checks
        $attempts = isset($taskData['validate_attempts']) ? (int)$taskData['validate_attempts'] : 0;
        if(!$reason && $attempts >= self::DNS_MAX_VALIDATE_ATTEMPTS)
        {
            $reason = 'Maximum DNS validation attempts reached (' . $attempts . ')';
        }

        if(!$reason)
        {
            return $taskData;
        }

        $commonName = isset($order['orderData']['common_name']['name']) ? $order['orderData']['common_name']['name'] : $task->domain;

        try
        {
            $approvers = self::pickApproverEmails($api, $dnsDomains);
            if(!empty($taskData['dns_fallback_email']) && in_array($commonName, $dnsDomains))
            {
                $approvers[$commonName] = $taskData['dns_fallback_email'];
            }

            $switched = [];
            foreach($dnsDomains as $dnsDomain)
            {
                if(empty($approvers[$dnsDomain]))
                {
                    throw new \Exception('No approver emails available for domain: ' . $dnsDomain);
                }
                $api->changeApprovers($apiOrderId, ['domain' => ['name' => $dnsDomain, 'validation_method' => $approvers[$dnsDomain]]]);
                $switched[$dnsDomain] = $approvers[$dnsDomain];
            }

            self::updateStoredValidationMethods($serviceId, $api, $apiOrderId, $switched);

            $taskData['dns_switched_to_email'] = true;
            $taskData['dns_switch_time']       = time();

            $mainEmail = isset($switched[$commonName]) ? $switched[$commonName] : reset($switched);
            self::log($clientId, $serviceId, 'info', '[Validate] ' . $reason . '. Switched validation method to EMAIL (' . implode(', ', $switched) . ') via API.');

            if(!AutoInstallEmailTemplates::sendDnsToEmailSwitchEmail($serviceId, $commonName, $mainEmail))
            {
                self::log($clientId, $serviceId, 'error', '[Validate] Failed to send DNS to Email switch notification to the client');
            }
        }
        catch(\Exception $e)
        {
            $taskData['dns_switch_failures'] = (isset($taskData['dns_switch_failures']) ? (int)$taskData['dns_switch_failures'] : 0) + 1;
            if($taskData['dns_switch_failures'] >= self::MAX_DNS_SWITCH_ATTEMPTS)
            {
                // Stop retrying - the client can still change the method manually
                $taskData['dns_switched_to_email'] = true;
            }
            self::log($clientId, $serviceId, 'error', '[Validate] Failed to switch DNS to Email: ' . $e->getMessage());
        }

        return $taskData;
    }

    /**
     * Keeps the order data stored for the client area in sync (same as home::changeApprovalMethodHTML).
     */
    protected static function updateStoredValidationMethods($serviceId, SSLTrustCenterApi $api, $apiOrderId, array $methods)
    {
        $row = Request::where('serviceid', $serviceId)->where('name', 'certificate')->first();
        if(!$row)
        {
            return;
        }

        $certificate  = json_decode(\decrypt($row->request), true);
        $newOrderData = isset($certificate['orderData']['orderData']) ? $certificate['orderData']['orderData'] : [];

        foreach($methods as $domainName => $method)
        {
            if(isset($newOrderData['common_name']['name']) && $newOrderData['common_name']['name'] == $domainName)
            {
                $newOrderData['common_name']['validation_method'] = $method;
            }
            if(isset($newOrderData['alternative_names']) && is_array($newOrderData['alternative_names']))
            {
                foreach($newOrderData['alternative_names'] as $key => $san)
                {
                    if($san['name'] == $domainName)
                    {
                        $newOrderData['alternative_names'][$key]['validation_method'] = $method;
                    }
                }
            }
        }

        $newData                               = $api->getOrder($apiOrderId, $serviceId);
        $certificate['orderData']['orderData'] = $newOrderData;
        $certificate['orderData']['order']     = $newData['order'];
        $certificate['orderData']['items']     = $newData['items'];
        Request::updateOrderData($serviceId, $certificate);
    }

    protected static function uploadValidationFiles($task, array $taskData, array $domains)
    {
        $serviceId = (int)$task->service_id;
        $clientId  = (int)$task->client_id;
        $uploaded  = isset($taskData['files_uploaded']) ? (array)$taskData['files_uploaded'] : [];
        $failures  = isset($taskData['upload_failures']) ? (array)$taskData['upload_failures'] : [];

        foreach($domains as $domain)
        {
            $method = isset($domain['method']) ? strtolower($domain['method']) : '';
            if(!in_array($method, ['http', 'https']) || empty($domain['validation']['file_name']))
            {
                continue;
            }

            $name     = $domain['name'];
            $fileName = $domain['validation']['file_name'];
            $content  = isset($domain['validation']['value']) ? $domain['validation']['value'] : '';
            if(is_array($content))
            {
                $content = implode("\n", $content);
            }

            if(in_array($name, $uploaded) || (isset($failures[$name]) && $failures[$name] >= self::MAX_UPLOAD_ATTEMPTS))
            {
                continue;
            }

            try
            {
                // www.example.com is not listed by cPanel as a separate domain - fall back to example.com
                $lookupDomain  = $name;
                $cpanelService = Cpanel::getServiceByDomain($clientId, $lookupDomain);
                if($cpanelService === false && strpos($name, 'www.') === 0)
                {
                    $lookupDomain  = substr($name, 4);
                    $cpanelService = Cpanel::getServiceByDomain($clientId, $lookupDomain);
                }
                if($cpanelService === false)
                {
                    throw new \Exception('cPanel account not found for domain: ' . $name);
                }

                $cpanel = new Cpanel();
                $cpanel->setService($cpanelService);

                $docRoot   = $cpanel->getRootDirectory($cpanelService->user, $lookupDomain);
                $wellKnown = $docRoot . '/.well-known';
                $pkiDir    = $wellKnown . '/pki-validation';

                $cpanel->addDirectory($cpanelService->user, [
                    ['dir' => $docRoot, 'name' => '.well-known'],
                    ['dir' => $wellKnown, 'name' => 'pki-validation'],
                ]);
                $cpanel->saveFile($cpanelService->user, $fileName, $pkiDir . '/', $content);

                $uploaded[] = $name;
                self::log($clientId, $serviceId, 'success', '[Validate] Validation file uploaded - verify URL: ' .
                    $method . '://' . $name . '/.well-known/pki-validation/' . $fileName);
            }
            catch(\Exception $e)
            {
                $failures[$name] = (isset($failures[$name]) ? (int)$failures[$name] : 0) + 1;
                self::log($clientId, $serviceId, 'error', '[Validate] Failed to upload validation file for domain: ' . $name . ' - ' . $e->getMessage() .
                    ' | Manual upload required: /.well-known/pki-validation/' . $fileName);
            }
        }

        $taskData['files_uploaded']  = $uploaded;
        $taskData['upload_failures'] = $failures;

        return $taskData;
    }

    /**
     * Picks an approver email for every domain (admin@domain when available, otherwise the first one offered).
     * Returns [domain => email]; domains without any approver email are left out.
     */
    public static function pickApproverEmails(SSLTrustCenterApi $api, array $domains)
    {
        $baseDomains = [];
        foreach($domains as $domain)
        {
            $baseDomains[$domain] = strtolower(strpos($domain, '*.') === 0 ? substr($domain, 2) : $domain);
        }
        if(empty($baseDomains))
        {
            return [];
        }

        $result       = $api->getDomainEmails(array_values(array_unique($baseDomains)));
        $emailsByBase = [];
        foreach((array)$result as $row)
        {
            if(!isset($row['domain'], $row['emails']) || !is_array($row['emails']))
            {
                continue;
            }
            $rowDomain                = strtolower((string)$row['domain']);
            $rowDomain                = strpos($rowDomain, '*.') === 0 ? substr($rowDomain, 2) : $rowDomain;
            $emailsByBase[$rowDomain] = $row['emails'];
        }

        $picked = [];
        foreach($baseDomains as $domain => $base)
        {
            $candidates = isset($emailsByBase[$base]) ? $emailsByBase[$base] : [];
            if(empty($candidates))
            {
                continue;
            }
            $picked[$domain] = in_array('admin@' . $base, $candidates) ? 'admin@' . $base : reset($candidates);
        }

        return $picked;
    }

    // ------------------------------------------------------------------ //
    // Misc
    // ------------------------------------------------------------------ //
}
