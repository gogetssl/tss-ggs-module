<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Gateway\Endpoints;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\AwaitingConfigurationException;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\GatewayException;
use ModulesGarden\TSSGGSModule\App\Libs\Support\ClientText;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Billing\FinancePolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\LogPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\Support\SecretValue;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Libs\Logs\Logger;

/**
 * HTTP transport of the gateway API: the request, its log entries, and errors as readable texts / typed exceptions.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait Transport
{
    protected static function log($action, $request, $response, array $replace = [])
    {
        if(GeneralSettings::enabled('moduleLog'))
        {
            // credentials, EAB keys, SSO links and private keys never go to the log
            logModuleCall('tss_gss_module', $action, SecretValue::redact($request), SecretValue::redact($response), '', $replace);
        }
    }

    /**
     * Every gateway request and its answer goes to the module log (Logs page), so a failed order or sync can be traced
     * to the exact request. Secrets are masked, long bodies are cut, logging never breaks the request.
     */
    protected function logCall($method, $uri, $requestBody, $httpCode, $responseBody, $seconds, $error = '', array $whmcsLog = [])
    {
        $serviceHint       = $this->serviceHint;
        $this->serviceHint = null;

        try
        {
            $path   = strtok($uri, '?');
            $failed = $error !== '' || $httpCode < 200 || $httpCode >= 400;

            // cheap decision first; the service lookup is only needed for services in debug mode
            if(!LogPolicy::debugServices() && !LogPolicy::logApi($failed, $method, null))
            {
                return;
            }
            $reason = $error;
            if($error === '' && $failed)
            {
                $decoded = json_decode((string)$responseBody, true);
                $reason  = is_array($decoded) && isset($decoded['message']) && is_scalar($decoded['message']) ? (string)$decoded['message'] : '';
            }

            $data      = [];
            $serviceId = $serviceHint ?: self::serviceFromUri($uri);
            if(!LogPolicy::logApi($failed, $method, $serviceId))
            {
                return;
            }

            // the same decision governs the WHMCS Module Log (Utilities > Logs > Module Log), if that switch is on
            if($whmcsLog)
            {
                self::log($whmcsLog[0], $whmcsLog[1], $whmcsLog[2], $whmcsLog[3] ?? []);
            }
            if($serviceId)
            {
                $data['service'] = (int)$serviceId;
            }
            $data['api'] = [
                'provider'  => $this->type,
                'method'    => $method,
                'url'       => (strtoupper($this->type) === 'GGS' ? $this->apiUrl . $uri : '/whmcs/v2' . $uri) . ($this->isSandbox() && strtoupper($this->type) !== 'GGS' ? ' (sandbox)' : ''),
                'http_code' => $httpCode ?: 'no response',
                'duration'  => round($seconds, 2) . ' s',
                'request'   => self::logBody($requestBody),
                'response'  => self::logBody($error !== '' ? $error : $responseBody),
            ];

            $message = 'API ' . $method . ' ' . $path . ' - ' . ($error !== '' ? 'failed' : 'HTTP ' . $httpCode) . ($reason !== '' ? ': ' . $reason : '');
            $failed ? Logger::error($message, $data) : Logger::info($message, $data);
        }
        catch(\Throwable $e)
        {
            // the log must never break an API request
        }
    }

    /** The service an item / order ID of the request belongs to (tblsslorders keeps the link), null when unknown */
    protected static function serviceFromUri($uri)
    {
        if(!preg_match_all('~/(\d+)(?=/|$|\?)~', (string)$uri, $matches))
        {
            return null;
        }

        return \WHMCS\Database\Capsule::table('tblsslorders')->where('remoteid', (string)end($matches[1]))->value('serviceid') ?: null;
    }

    /** Body for the log: decoded JSON when possible, secrets masked, cut to fit the log column */
    protected static function logBody($body, $limit = 24000)
    {
        if($body === null || $body === '')
        {
            return '';
        }
        $decoded = is_string($body) ? json_decode($body, true) : null;
        $value   = SecretValue::redact(is_array($decoded) ? $decoded : $body);
        $json    = json_encode($value);
        if($json !== false && strlen($json) <= $limit)
        {
            return $value;
        }

        return ['truncated' => true, 'preview' => mb_strcut(is_string($value) ? $value : (string)$json, 0, $limit)];
    }

    protected function call($uri, $postData = [], $customMethod = false, $multilineResponse = false)
    {

        global $CONFIG;

        // what this client has always called itself to the vendor
        $versionModule = '1.0.0';
        $moduleName    = 'TSSGGSModule';

        $url = $this->apiUrl . $uri;

        $post = !empty($postData) ? true : false;
        $c    = curl_init($url);
        if($post && $customMethod === false)
        {
            curl_setopt($c, CURLOPT_POST, true);
        }

        if($customMethod !== false)
        {
            curl_setopt($c, CURLOPT_CUSTOMREQUEST, $customMethod);
        }

        $queryData = '';
        if(!empty($postData))
        {
            $queryData = json_encode($postData);
            curl_setopt($c, CURLOPT_POSTFIELDS, $queryData);
        }

        $headers   = [];
        $headers[] = 'Authorization: ' . $this->type . ' ' . $this->code . ':' . $this->key;
        $headers[] = 'User-Agent: whmcs/' . $CONFIG['Version'] . '|' . $moduleName . '/' . $versionModule;

        if($queryData !== '')
        {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($queryData);
        }
        elseif(in_array($customMethod, ['POST', 'PUT', 'PATCH'], true))
        {
            // Request without body (e.g. validation recheck) - some servers reject it without the length header
            $headers[] = 'Content-Length: 0';
        }

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLINFO_HEADER_OUT, true);
        curl_setopt($c, CURLOPT_HEADER, true);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, min(self::CONNECT_TIMEOUT, $this->timeout));
        curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);

        $started = microtime(true);
        $hint    = $this->serviceHint; // logCall() clears it
        $data    = curl_exec($c);
        $info    = curl_getinfo($c);
        $method  = $customMethod !== false ? strtoupper((string)$customMethod) : ($post ? 'POST' : 'GET');

        if($data === false)
        {
            $error = curl_error($c);
            curl_close($c);
            $this->logCall($method, $uri, $queryData, 0, '', microtime(true) - $started, $error, ['Time: ' . $info['total_time'] . ' ' . $uri, $queryData, $error]);
            throw new GatewayException(Edition::brandOf($this->type) . ' API request failed: ' . $error, 0);
        }

        $result = substr($data, $info['header_size']);

        $this->logCall($method, $uri, $queryData, (int)$info['http_code'], (string)$result, microtime(true) - $started, '', ['Time: ' . $info['total_time'] . ' ' . $uri, $info['request_header'] . $queryData, $data, array_values(array_filter([(string)$this->key], 'strlen'))]);

        curl_close($c);

        $httpCode  = (int)$info['http_code'];
        $isSuccess = $httpCode >= 200 && $httpCode < 300;

        if($httpCode === 204) // No Content
        {
            return [];
        }

        if(trim((string)$result) === '')
        {
            if($isSuccess)
            {
                return [];
            }
            throw new GatewayException(self::readableError('', $httpCode), $httpCode);
        }

        if($multilineResponse)
        {
            $result = '[' . str_replace("\n", ',', trim($result)) . ']';
        }

        $response = json_decode($result, true);
        if($response === null)
        {
            // an error page of a proxy (502 / 504) is reported with its HTTP code, not as an unreadable answer
            if(!$isSuccess)
            {
                throw new GatewayException(self::readableError('', $httpCode), $httpCode);
            }
            throw new GatewayException('Invalid response from API', $httpCode);
        }

        if(!$isSuccess)
        {
            // an order refused for lack of funds: tell the administrators at once (Settings > Finance > Reseller balance)
            if(isset($response['message']) && FinancePolicy::isBalanceError($response['message']))
            {
                try
                {
                    FinancePolicy::balanceAlert($response['message'], $hint, $this->type);
                }
                catch(\Throwable $e)
                {
                }
            }

            $gatewayCode    = is_scalar($response['code'] ?? null) ? (string)$response['code'] : '';
            $gatewayMessage = is_scalar($response['message'] ?? null) ? (string)$response['message'] : '';

            // the order waits for its validation method: a sentence the client can act on, not the gateway's 502
            if(stripos($gatewayMessage, self::AWAITING_CONFIGURATION) !== false)
            {
                throw new AwaitingConfigurationException(ClientText::get('awaitingValidationMethod',
                    'The validation method for this order has not been set yet. Please choose how to validate your domain and try again.'), $httpCode, $gatewayCode, $gatewayMessage);
            }

            throw new GatewayException(self::readableError($gatewayMessage, $httpCode), $httpCode, $gatewayCode, $gatewayMessage);
        }

        return $response;
    }

    /**
     * The order was submitted but its validation method was never chosen: the gateway answers the order details with
     * 502 GEX-4 "Unexpected validation method: not yet set" (seen 2026-11-24). The order waits for its configuration,
     * it has not failed.
     */
    public static function isAwaitingConfiguration(\Throwable $e)
    {
        return $e instanceof AwaitingConfigurationException || stripos($e->getMessage(), self::AWAITING_CONFIGURATION) !== false;
    }

    /**
     * getOrder(), or $fallback when the gateway can not show the order yet because its validation method is not set:
     * for the reads right after a client's action that the gateway did accept (order placed, reissue, method change)
     */
    public function getOrderOr($orderId, $serviceId, array $fallback)
    {
        try
        {
            return $this->getOrder($orderId, $serviceId);
        }
        catch(AwaitingConfigurationException $e)
        {
            return $fallback;
        }
    }

    /**
     * Gateway error as a sentence a client can read (it is shown in the client area as is); the HTTP code stays in
     * the exception code and in the API log. "Vendor returns error:ErrorCode:x|ErrorMessage:y" becomes "y".
     */
    public static function readableError($message, $httpCode)
    {
        $message = trim((string)$message);
        if(preg_match('/ErrorMessage:\s*(.+)$/is', $message, $m))
        {
            $message = trim($m[1]);
        }
        $message = trim(preg_replace('/^Vendor returns error:\s*/i', '', $message));
        $message = rtrim($message, '!.') ;

        if($message === '')
        {
            return $httpCode >= 500
                ? 'The SSL service is temporarily unavailable (HTTP ' . (int)$httpCode . '). Please try again later.'
                : 'The SSL service could not process the request (HTTP ' . (int)$httpCode . ').';
        }

        return $httpCode >= 500
            ? 'The SSL service could not complete the request: ' . $message . '. Please try again later.'
            : $message . '.';
    }
}
