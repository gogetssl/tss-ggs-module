<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * cPanel / WHM client used by the SSL AutoInstall feature.
 * Ported from SSLCENTERWHMCS (eHelpers\Cpanel + eModels\cpanelservices\Service).
 */
class Cpanel
{
    private $service;
    protected $ch = false;
    protected $sessionUrl;
    protected $cookieJar;

    /**
     * All cPanel hosting services owned by the client.
     */
    public static function getServices($userId)
    {
        return Capsule::table('tblhosting')
            ->select([
                'tblhosting.id',
                'tblhosting.domain',
                'tblhosting.username as user',
                'tblproducts.name',
                'tblservers.hostname',
                'tblservers.ipaddress',
                'tblservers.secure',
                'tblservers.port',
                'tblservers.accesshash as hash',
                'tblservers.username',
                'tblservers.password',
                'tblhosting.packageid'
            ])
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->join('tblservers', 'tblhosting.server', '=', 'tblservers.id')
            ->where('tblproducts.servertype', 'cpanel')
            ->where('tblhosting.userid', (int)$userId)
            ->get();
    }

    /**
     * Finds the client's cPanel service hosting the given domain, false when none.
     */
    public static function getServiceByDomain($userId, $domain)
    {
        $domain = strtolower(trim((string)$domain));

        foreach(self::getServices($userId) as $service)
        {
            try
            {
                $cpanel = new self();
                $cpanel->setService($service);
                foreach($cpanel->listDomains($service->user) as $cpanelDomain)
                {
                    if(strtolower($cpanelDomain) === $domain)
                    {
                        return $service;
                    }
                }
            }
            catch(\Exception $e)
            {
                \logActivity('TSSGGSModule cPanel: ' . $e->getMessage(), 0);
            }
        }

        return false;
    }

    /**
     * Domains from all client's cPanel accounts merged into one grouped list.
     * Returns null when the client has no reachable cPanel account with domains.
     */
    public static function getMergedDomainsGrouped($userId)
    {
        $services = self::getServices($userId);
        if($services->isEmpty())
        {
            return null;
        }

        $merged = ['main' => null, 'addon' => [], 'parked' => [], 'subdomain' => []];
        $seen   = [];

        foreach($services as $svcInfo)
        {
            try
            {
                $cpanel = new self();
                $cpanel->setService($svcInfo);
                $grouped = $cpanel->listDomainsGrouped($svcInfo->user);
            }
            catch(\Exception $e)
            {
                // Server unreachable or misconfigured - skip it, keep collecting from the rest
                continue;
            }

            if(!empty($grouped['main']) && !isset($seen[$grouped['main']]))
            {
                $seen[$grouped['main']] = true;
                if(!$merged['main'])
                {
                    $merged['main'] = $grouped['main'];
                }
                else
                {
                    $merged['addon'][] = $grouped['main'];
                }
            }
            foreach($grouped['addon'] as $d)
            {
                if(!isset($seen[$d]))
                {
                    $seen[$d]          = true;
                    $merged['addon'][] = $d;
                }
            }
            foreach($grouped['parked'] as $d)
            {
                if(!isset($seen[$d]))
                {
                    $seen[$d]           = true;
                    $merged['parked'][] = $d;
                }
            }
            foreach($grouped['subdomain'] as $s)
            {
                $subKey = is_array($s) ? $s['domain'] : $s;
                if(!isset($seen[$subKey]))
                {
                    $seen[$subKey]         = true;
                    $merged['subdomain'][] = $s;
                }
            }
        }

        if($merged['main'] || !empty($merged['addon']) || !empty($merged['subdomain']))
        {
            return $merged;
        }

        return null;
    }

    private function logCall($action, $request, $response, $processed = '', $replacements = [])
    {
        logModuleCall('TSSGGSModule_cpanel', $action, $request, $response, $processed, $replacements);
    }

    private function getReplacements()
    {
        return [$this->service['password'] ?? '', $this->service['hash'] ?? ''];
    }

    protected function initiateConnection($username)
    {
        $sessionDetails  = $this->getSessionDetails($username);
        $decodedResponse = json_decode($sessionDetails, true);
        if(!$decodedResponse)
        {
            $this->ch = false;
            return false;
        }

        $this->setSessionDetails($decodedResponse);
    }

    protected function setSessionDetails($decodedResponse)
    {
        $sessionUrl = $decodedResponse['data']['url'];
        if(!$this->cookieJar)
        {
            $this->cookieJar = tempnam(sys_get_temp_dir(), 'tssggs_cp');
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, []);
        curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieJar);
        curl_setopt($this->ch, CURLOPT_URL, $sessionUrl);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        $resultorg = curl_exec($this->ch);

        if($resultorg == false)
        {
            throw new \Exception(curl_error($this->ch));
        }
        $this->sessionUrl = preg_replace('{/login(?:/)??.*}', '', $sessionUrl);
    }

    /**
     * Builds the Authorization header for WHM/cPanel API calls (WHM access hash / API token).
     */
    private function buildAuthHeader()
    {
        $hash = $this->service['hash'] ?? '';
        if(empty($hash))
        {
            return [];
        }
        $clean = str_replace(["\r", "\n"], '', $hash);
        return ['Authorization: WHM ' . $this->service['username'] . ':' . $clean];
    }

    protected function getSessionDetails($username)
    {
        $query    = ($this->service['ssl'] ? 'https://' . $this->service['hostname'] . ':2087' : 'http://' . $this->service['hostname'] . ':2086') .
            "/json-api/create_user_session?api.version=1&user=" . $username . "&service=cpaneld";
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 15);
        $authHeader = $this->buildAuthHeader();

        if(!empty($authHeader))
        {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $authHeader);
        }
        elseif($this->service['password'] != '')
        {
            curl_setopt($this->ch, CURLOPT_USERPWD, $this->service['username'] . ':' . $this->service['password']);
        }
        curl_setopt($this->ch, CURLOPT_URL, $query);
        $resultorg = curl_exec($this->ch);

        $info   = curl_getinfo($this->ch);
        $result = substr($resultorg, $info['header_size']);
        if($resultorg == false)
        {
            throw new \Exception(curl_error($this->ch));
        }
        return $result;
    }

    protected function exec($name, $api, $params = array())
    {
        $query = $this->sessionUrl . $api . $name;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_URL, $query);
        if(!empty($params))
        {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        }
        $resultorg = curl_exec($this->ch);
        $curl_info = curl_getinfo($this->ch);
        if($resultorg == false)
        {
            $error = curl_error($this->ch);
            $this->logCall($api . $name, $this->sanitizeParams($params), 'cURL error: ' . $error, '', $this->getReplacements());
            throw new \Exception($error);
        }
        $result = substr($resultorg, $curl_info['header_size']);
        $resp   = json_decode($result);

        $this->logCall(
            $api . $name,
            $this->sanitizeParams($params),
            $result,
            isset($resp->errors) ? $resp->errors : (isset($resp->data) ? $resp->data : $resp),
            $this->getReplacements()
        );

        return $resp;
    }

    private function sanitizeParams(array $params)
    {
        $safe = [];
        foreach($params as $k => $v)
        {
            if(in_array($k, ['cert', 'key', 'cabundle'], true))
            {
                $safe[$k] = '[' . strlen((string)$v) . ' bytes]';
            }
            else
            {
                $safe[$k] = $v;
            }
        }
        return $safe;
    }

    public function listDomains($username)
    {
        $grouped = $this->listDomainsGrouped($username);

        $domains = [];
        if(!empty($grouped['main']))
        {
            $domains[] = $grouped['main'];
        }
        foreach(array_merge($grouped['addon'], $grouped['parked']) as $domain)
        {
            $domains[] = $domain;
        }
        foreach($grouped['subdomain'] as $sub)
        {
            $domains[] = $sub['domain'];
        }

        return $domains;
    }

    public function listDomainsGrouped($username)
    {
        $this->initiateConnection($username);
        $response = $this->exec("list_domains", "/execute/DomainInfo/");

        $grouped = [
            'main'      => null,
            'addon'     => [],
            'parked'    => [],
            'subdomain' => [],
        ];

        if(isset($response->data->main_domain) && !empty($response->data->main_domain))
        {
            $grouped['main'] = $response->data->main_domain;
        }

        if(isset($response->data->addon_domains) && !empty($response->data->addon_domains))
        {
            foreach($response->data->addon_domains as $domain)
            {
                $grouped['addon'][] = $domain;
            }
        }

        if(isset($response->data->parked_domains) && !empty($response->data->parked_domains))
        {
            foreach($response->data->parked_domains as $domain)
            {
                $grouped['parked'][] = $domain;
            }
        }

        if(isset($response->data->sub_domains) && !empty($response->data->sub_domains))
        {
            $allKnownParents = [];
            if($grouped['main'])
            {
                $allKnownParents[] = $grouped['main'];
            }
            foreach($grouped['addon'] as $d)
            {
                $allKnownParents[] = $d;
            }

            foreach($response->data->sub_domains as $domain)
            {
                $parent = null;
                $parts  = explode('.', $domain);
                if(count($parts) > 2)
                {
                    $possibleParent = implode('.', array_slice($parts, 1));
                    if(in_array($possibleParent, $allKnownParents))
                    {
                        $parent = $possibleParent;
                    }
                }
                $grouped['subdomain'][] = ['domain' => $domain, 'parent' => $parent];
            }
        }

        return $grouped;
    }

    public function setService($service)
    {
        $this->service['hostname']  = $service->hostname;
        $this->service['ipaddress'] = $service->ipaddress;
        $this->service['ssl']       = $service->secure == 'on';
        $this->service['port']      = $service->port;
        $this->service['hash']      = $service->hash;
        $this->service['username']  = $service->username;
        $this->service['password']  = decrypt($service->password);
    }

    public function getRootDirectory($username, $domain)
    {
        $this->initiateConnection($username);
        $response = $this->exec("single_domain_data", "/execute/DomainInfo/", [
            'domain'                       => $domain,
            'return_https_redirect_status' => '1'
        ]);
        if(isset($response->data->documentroot) && !empty($response->data->documentroot))
        {
            return $response->data->documentroot;
        }
        throw new \Exception(isset($response->errors[0]) ? $response->errors[0] : 'Unable to get document root for ' . $domain);
    }

    public function installSSL($username, $domain, $cert, $key, $cabundle)
    {
        $this->initiateConnection($username);

        $response = $this->exec("install_ssl", "/execute/SSL/", [
            'domain'   => $domain,
            'cert'     => $cert,
            'key'      => $key,
            'cabundle' => $cabundle,
        ]);

        $sslLog = ['domain' => $domain, 'cert' => '[' . strlen($cert) . ' bytes]', 'key' => '[' . strlen($key) . ' bytes]', 'cabundle' => '[' . strlen($cabundle) . ' bytes]'];
        if(isset($response->errors) && !empty($response->errors))
        {
            if(strpos($response->errors[0], 'install_ssl was handled successfully') !== false)
            {
                $this->logCall('installSSL', $sslLog, 'Success (reported via errors field): ' . $response->errors[0], 'installed', $this->getReplacements());
                return true;
            }
            $this->logCall('installSSL', $sslLog, 'Error: ' . $response->errors[0], 'failed', $this->getReplacements());
            throw new \Exception($response->errors[0]);
        }

        $this->logCall('installSSL', $sslLog, json_encode($response), 'success - no errors', $this->getReplacements());
        return true;
    }

    public function saveFile($username, $file, $dir, $content)
    {
        $this->initiateConnection($username);

        $requestLog = ['dir' => $dir, 'file' => $file, 'content' => $content];
        $fileParams = [
            'dir'          => $dir,
            'file'         => $file,
            'from_charset' => 'UTF-8',
            'to_charset'   => 'ASCII',
            'content'      => $content,
            'fallback'     => '0',
        ];

        $response = $this->exec("save_file_content", "/execute/Fileman/", $fileParams);

        if(isset($response->errors) && !empty($response->errors))
        {
            $error = $response->errors[0];

            // On some cPanel versions save_file_content fails when the file
            // already exists. Delete it first and retry once.
            $isExistsError = stripos($error, 'exist') !== false
                || stripos($error, 'already') !== false
                || stripos($error, 'create') !== false;

            if($isExistsError)
            {
                $this->exec("delete_files", "/execute/Fileman/", [
                    'files' => json_encode([$dir . $file]),
                ]);

                $response = $this->exec("save_file_content", "/execute/Fileman/", $fileParams);
            }

            if(isset($response->errors) && !empty($response->errors))
            {
                $this->logCall('saveFile', $requestLog, 'Error: ' . $response->errors[0], 'failed', $this->getReplacements());
                throw new \Exception($response->errors[0]);
            }
        }

        $this->logCall('saveFile', $requestLog, json_encode($response), 'success', $this->getReplacements());
        return true;
    }

    /**
     * DNS zone (main / addon / parked domain of the account) that holds the given host name.
     */
    public function getZoneForDomain($username, $host)
    {
        $host    = strtolower(rtrim(trim($host), '.'));
        $grouped = $this->listDomainsGrouped($username);
        $zones   = array_merge($grouped['main'] ? [$grouped['main']] : [], $grouped['addon'], $grouped['parked']);

        $best = null;
        foreach($zones as $zone)
        {
            $zone = strtolower($zone);
            if($host === $zone || substr($host, -strlen('.' . $zone)) === '.' . $zone)
            {
                if($best === null || strlen($zone) > strlen($best))
                {
                    $best = $zone;
                }
            }
        }

        return $best;
    }

    /**
     * Whether the zone already contains the record (name + type, and the same value when given).
     */
    public function zoneRecordExists($user, $zone, $name, $type, $value = null)
    {
        $result = $this->get([
            'cpanel_jsonapi_user'       => $user,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module'     => 'ZoneEdit',
            'cpanel_jsonapi_func'       => 'fetchzone_records',
            'domain'                    => $zone,
            'name'                      => $name,
            'type'                      => $type,
        ]);

        $records = isset($result->cpanelresult->data) && is_array($result->cpanelresult->data) ? $result->cpanelresult->data : [];
        foreach($records as $record)
        {
            if(strcasecmp(rtrim((string)$record->name, '.'), rtrim($name, '.')) !== 0 || strcasecmp((string)$record->type, $type) !== 0)
            {
                continue;
            }
            if($value === null)
            {
                return true;
            }
            $current = strtoupper($type) === 'TXT'
                ? (isset($record->txtdata) ? (string)$record->txtdata : '')
                : (isset($record->cname) ? (string)$record->cname : '');
            if(strcasecmp(rtrim(trim($current, '"'), '.'), rtrim(trim($value, '"'), '.')) === 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a CNAME / TXT record to the zone ($name as FQDN with a trailing dot).
     */
    public function addZoneRecord($user, $zone, $name, $type, $value, $ttl = 3600)
    {
        $params = [
            'cpanel_jsonapi_user'       => $user,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module'     => 'ZoneEdit',
            'cpanel_jsonapi_func'       => 'add_zone_record',
            'domain'                    => $zone,
            'name'                      => $name,
            'type'                      => strtoupper($type),
            'class'                     => 'IN',
            'ttl'                       => (int)$ttl,
        ];

        if(strtoupper($type) === 'TXT')
        {
            $params['txtdata'] = $value;
        }
        else
        {
            $params['cname'] = $value;
        }

        $result = $this->get($params);

        if(isset($result->cpanelresult->data[0]->result->status) && !$result->cpanelresult->data[0]->result->status)
        {
            $reason = isset($result->cpanelresult->data[0]->result->statusmsg) ? $result->cpanelresult->data[0]->result->statusmsg : 'Unknown error';
            throw new \Exception($reason);
        }

        return true;
    }

    public function addDirectory($user, $directories)
    {
        foreach($directories as $newDir)
        {
            try
            {
                $this->get([
                    'cpanel_jsonapi_user'       => $user,
                    'cpanel_jsonapi_apiversion' => '2',
                    'cpanel_jsonapi_module'     => 'Fileman',
                    'cpanel_jsonapi_func'       => 'mkdir',
                    'path'                      => $newDir['dir'],
                    'name'                      => $newDir['name'],
                ]);
            }
            catch(\Exception $ex)
            {
                // Directory most likely exists already
                continue;
            }
        }
        return true;
    }

    private function get($params = array())
    {
        $url = ($this->service['ssl'] ? 'https://' . $this->service['hostname'] . ':2087' : 'http://' . $this->service['hostname'] . ':2086') . '/json-api/cpanel';
        $ch  = curl_init();
        if(is_array($params))
        {
            $url .= '?';
            foreach($params as $key => $value)
            {
                $value = urlencode($value);
                $url   .= "{$key}={$value}&";
            }
        }
        $url       = trim($url, '&');
        $chOptions = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30
        );

        $authHeader = $this->buildAuthHeader();
        if(!empty($authHeader))
        {
            $chOptions[CURLOPT_HTTPHEADER] = $authHeader;
        }
        elseif($this->service['password'] != '')
        {
            $chOptions[CURLOPT_USERPWD] = $this->service['username'] . ':' . $this->service['password'];
        }
        else
        {
            throw new \Exception('Password or Access key is required');
        }

        $logAction = isset($params['cpanel_jsonapi_func']) ? $params['cpanel_jsonapi_func'] : 'cpanel_api';
        $logParams = array_diff_key($params, array_flip(['cpanel_jsonapi_user']));

        curl_setopt_array($ch, $chOptions);
        $out = curl_exec($ch);

        if(curl_errno($ch))
        {
            $curlErr = curl_errno($ch) . ' - ' . curl_error($ch);
            $this->logCall($logAction, $logParams, 'cURL Error: ' . $curlErr, '', $this->getReplacements());
            throw new \Exception("cURL Error: " . $curlErr);
        }

        $out_info = curl_getinfo($ch);
        if($out_info['http_code'] == 301 || $out_info['http_code'] == 302)
        {
            $this->logCall($logAction, $logParams, 'HTTP ' . $out_info['http_code'] . ' redirect - SSL required', '', $this->getReplacements());
            throw new \Exception('Module require SSL');
        }

        curl_close($ch);

        if(strpos($out, 'SSL encryption is required for access to this server') !== false)
        {
            throw new \Exception('SSL encryption is required for access to this server');
        }

        $a = json_decode($out);
        if(!$a)
        {
            throw new \Exception('Unable to parse response');
        }

        if(isset($a->cpanelresult->error) && $a->cpanelresult->error && isset($a->cpanelresult->data->result) && $a->cpanelresult->data->result == 0)
        {
            throw new \Exception($a->cpanelresult->error);
        }

        if(isset($a->status) && $a->status == 0)
        {
            throw new \Exception($a->statusmsg ?: 'Unknown Error');
        }

        if(isset($a->data->result) && $a->data->result == 0)
        {
            throw new \Exception($a->data->reason ?: 'Unknown Error');
        }

        if(!empty($a->error))
        {
            throw new \Exception($a->error);
        }

        if(isset($a->metadata->result) && $a->metadata->result == 0)
        {
            $this->logCall($logAction, $logParams, $out, 'metadata result=0: ' . $a->metadata->reason, $this->getReplacements());
            throw new \Exception($a->metadata->reason);
        }

        $this->logCall($logAction, $logParams, $out, isset($a->data) ? $a->data : $a, $this->getReplacements());

        return isset($a->data) ? $a->data : $a;
    }

    public function __destruct()
    {
        if($this->cookieJar && file_exists($this->cookieJar))
        {
            @unlink($this->cookieJar);
        }
    }
}
