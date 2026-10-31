<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger;
use WHMCS\Database\Capsule;

/**
 * SSL AutoInstall (via cPanel).
 *
 * Per-service configuration (install domain, encrypted private key, summary status) is kept in the Requests table
 * (name = 'autoinstall'). Work is done by cron through the AutoInstallQueue tasks:
 *  - validate: watches the order, uploads HTTP/HTTPS validation files to cPanel, switches DNS validation to email
 *              when it can not be completed, queues "install" once the certificate is issued,
 *  - install:  installs the certificate in cPanel and notifies the client,
 *  - reissue:  reissues the certificate (same CSR / private key) shortly before it expires within a subscription.
 * Every step is written to the AutoInstall service log (client area + admin area).
 */
class AutoInstallHelpers
{
    const REQUEST_NAME = 'autoinstall';

    const STATUS_AWAITING  = 'awaiting_validation';
    const STATUS_INSTALLED = 'installed';
    const STATUS_FAILED    = 'failed';
    const STATUS_DISABLED  = 'disabled';

    /**
     * DNS validation: the record is created in the client's cPanel zone automatically. When the validation is not
     * completed in time, the method is switched to email and the client is notified (same rules as SSLCENTERWHMCS):
     *  - wildcard: after the "Wildcard DNS Validation Timeout (minutes)" SSL setting since the order was placed,
     *  - other:    after DNS_MAX_VALIDATE_ATTEMPTS validation checks (cron runs).
     */
    const DEFAULT_WILDCARD_DNS_TIMEOUT_MINUTES = 65;
    const DNS_MAX_VALIDATE_ATTEMPTS       = 48;
    const DNS_RECORD_TTL                  = 3600;
    const VALIDATION_WARNING_AFTER_CHECKS = 5;
    /** Default of the "Auto Reissue Days Before Expiration" SSL setting (0 = disabled) */
    const DEFAULT_AUTO_REISSUE_DAYS_BEFORE_EXPIRE = 7;
    const MAX_UPLOAD_ATTEMPTS             = 10;
    const MAX_DNS_SWITCH_ATTEMPTS         = 5;
    const SAME_CERT_MAX_CHECKS            = 288;

    const FINAL_ORDER_STATUSES = ['cancelled', 'expired', 'rejected', 'filed', 'revoked', 'refunded'];

    // ------------------------------------------------------------------ //
    // Per-service configuration
    // ------------------------------------------------------------------ //

    public static function create($serviceId, array $data)
    {
        unset($data['status']);
        Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->delete();
        Request::insert([
            'serviceid'  => $serviceId,
            'name'       => self::REQUEST_NAME,
            'request'    => \encrypt(json_encode($data)),
            'status'     => self::STATUS_AWAITING,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function get($serviceId)
    {
        $row = Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->first();
        if(!$row)
        {
            return null;
        }

        $data           = json_decode(\decrypt($row->request), true) ?: [];
        $data['status'] = $row->status;

        return $data;
    }

    public static function save($serviceId, array $data, $status)
    {
        unset($data['status']);
        Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->update([
            'request'    => \encrypt(json_encode($data)),
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Data for the client / admin area (no private key).
     */
    public static function getStatusForView($serviceId, $logsLimit = 10)
    {
        $data = self::get($serviceId);
        if(!$data)
        {
            return null;
        }

        $logs = [];
        try
        {
            foreach(AutoInstallQueue::getLogs($serviceId, $logsLimit) as $log)
            {
                $logs[] = ['date' => $log->created_at, 'type' => $log->type, 'message' => $log->message];
            }
        }
        catch(\Exception $e)
        {
        }

        return [
            'status'       => $data['status'],
            'domain'       => isset($data['domain']) ? $data['domain'] : '',
            'last_error'   => isset($data['last_error']) ? $data['last_error'] : '',
            'installed_at' => isset($data['installed_at']) ? $data['installed_at'] : '',
            'valid_till'   => isset($data['valid_till']) ? $data['valid_till'] : '',
            'logs'         => $logs,
        ];
    }

    public static function log($clientId, $serviceId, $type, $message)
    {
        AutoInstallQueue::addLog($clientId, $serviceId, $type, $message);

        try
        {
            if($type === 'error')
            {
                Logger::error('[AutoInstall] ' . $message, ['service' => $serviceId]);
            }
            else
            {
                Logger::info('[AutoInstall] ' . $message, ['service' => $serviceId]);
            }
        }
        catch(\Throwable $e)
        {
            // Logger is not available outside of the addon context - the service log above is enough
        }
    }

    // ------------------------------------------------------------------ //
    // Events
    // ------------------------------------------------------------------ //

    /**
     * Client submitted the SSL configuration with "Autoinstall".
     */
    public static function onConfigured($serviceId, $userId, array $config, array $validateTaskData)
    {
        self::create($serviceId, $config);
        AutoInstallQueue::cancelOpenTasks($serviceId, 'Replaced by a new configuration');
        AutoInstallQueue::addTask($userId, $serviceId, 'validate', $validateTaskData);

        self::log($userId, $serviceId, 'success', 'SSL order placed for domain: ' . $config['domain'] .
            ' (API order #' . $validateTaskData['api_order_id'] . ', validation: ' . strtoupper($config['dcv_method']) . ') - AutoInstall validation task created');
    }

    /**
     * The client reissued the certificate manually with a new CSR - the stored private key no longer matches.
     */
    public static function onManualReissue($serviceId)
    {
        $state = self::get($serviceId);
        if(!$state || $state['status'] === self::STATUS_DISABLED)
        {
            return;
        }

        $userId = (int)Capsule::table('tblhosting')->where('id', $serviceId)->value('userid');

        AutoInstallQueue::cancelOpenTasks($serviceId, 'Certificate reissued manually');
        $state['last_error'] = '';
        self::save($serviceId, $state, self::STATUS_DISABLED);

        self::log($userId, $serviceId, 'info', 'Certificate reissued manually with a new CSR - AutoInstall has been disabled for this service. ' .
            'The new certificate has to be installed manually.');
    }

    /**
     * The certificate was renewed (same CSR / private key) - install the renewed certificate as well.
     * $targetServiceId differs from $sourceServiceId when the renewal created a new WHMCS service.
     */
    /**
     * Called by the Renew action once the renewal order is placed (same as SSLCENTERWHMCS Renew::afterRenew):
     *  - AutoInstall: queue validation + installation of the renewed certificate, send "Renewal Started",
     *  - otherwise:   send "Certificate Autorenewed".
     */
    public static function afterRenewal($sourceServiceId, $targetServiceId, $newOrderId)
    {
        $state      = self::get($sourceServiceId);
        $certExpiry = isset($state['valid_till']) ? (string)$state['valid_till'] : '';
        $queued     = self::onRenewal($sourceServiceId, $targetServiceId, $newOrderId);

        $hosting = Capsule::table('tblhosting')->where('id', $targetServiceId)->first();
        $userId  = $hosting ? (int)$hosting->userid : 0;
        $domain  = $hosting ? (string)$hosting->domain : '';

        if($queued)
        {
            $daysLeft = $certExpiry ? (int)round((strtotime($certExpiry) - time()) / 86400) : 0;
            if(!AutoInstallEmailTemplates::sendRenewalStartedEmail($targetServiceId, $state['domain'], $certExpiry, $daysLeft))
            {
                self::log($userId, $targetServiceId, 'error', '[Renew] Failed to send Renewal Started email to the client');
            }
            return;
        }

        $commonName = $domain;
        $request    = Request::where('serviceid', $targetServiceId)->where('name', 'renewOrder')->first();
        if($request && $request->request)
        {
            $requestData = json_decode(\decrypt($request->request), true);
            if(!empty($requestData['common_name']['name']))
            {
                $commonName = $requestData['common_name']['name'];
            }
        }

        AutoInstallEmailTemplates::sendRenewalAutorenewedEmail($targetServiceId, $domain, (string)$newOrderId, $commonName);
    }

    /**
     * Queues the installation of the renewed certificate. Returns true when AutoInstall is used for the service.
     */
    public static function onRenewal($sourceServiceId, $targetServiceId, $newOrderId)
    {
        $state = self::get($sourceServiceId);
        if(!$state || $state['status'] === self::STATUS_DISABLED || (int)$newOrderId <= 0)
        {
            return false;
        }

        $userId = (int)Capsule::table('tblhosting')->where('id', $targetServiceId)->value('userid');

        $config               = $state;
        $config['last_error'] = '';
        unset($config['reissue_for_valid_till']);

        if((int)$sourceServiceId === (int)$targetServiceId)
        {
            self::save($targetServiceId, $config, self::STATUS_AWAITING);
        }
        else
        {
            self::create($targetServiceId, $config);
        }

        AutoInstallQueue::cancelOpenTasks($targetServiceId, 'Replaced by renewal');

        $taskData = [
            'domain'       => $config['domain'],
            'api_order_id' => (int)$newOrderId,
            'is_renewal'   => true,
            'is_wildcard'  => !empty($config['is_wildcard']),
        ];
        if(isset($config['dcv_method']) && $config['dcv_method'] === 'dns')
        {
            $taskData['dns_check_start'] = time();
        }
        if(!empty($config['dns_fallback_email']))
        {
            $taskData['dns_fallback_email'] = $config['dns_fallback_email'];
        }
        AutoInstallQueue::addTask($userId, $targetServiceId, 'validate', $taskData);

        self::log($userId, $targetServiceId, 'info', '[Renew] Certificate renewal placed (API order #' . (int)$newOrderId .
            ') - the renewed certificate will be installed automatically on ' . $config['domain']);

        return true;
    }

    // ------------------------------------------------------------------ //
    // Cron
    // ------------------------------------------------------------------ //

    /**
     * Processes the whole AutoInstall queue. Returns the last error message (for CronCheck) or an empty string.
     */
    public static function processQueue()
    {
        // The queue is run by two crons (SSLAutoInstall + SSLCertificatesProcessing) - only one run at a time,
        // otherwise hasOpenTask()/addTask() in scanAutoReissue() and the stuck-task reset can duplicate tasks
        $lock = Capsule::select("SELECT GET_LOCK('tssggs_autoinstall_queue', 0) AS l");
        if(empty($lock) || (int)$lock[0]->l !== 1)
        {
            return '';
        }

        try
        {
            return self::processQueueLocked();
        }
        finally
        {
            Capsule::select("SELECT RELEASE_LOCK('tssggs_autoinstall_queue')");
        }
    }

    protected static function processQueueLocked()
    {
        // A task in "processing" this long means the previous run died (concurrent runs are excluded by the lock)
        AutoInstallQueue::resetStuckProcessing(60);

        $lastError = '';

        foreach(['validate', 'install', 'reissue'] as $type)
        {
            foreach(AutoInstallQueue::getPendingTasks($type, 20) as $task)
            {
                if(!AutoInstallQueue::claim($task->id))
                {
                    continue;
                }

                try
                {
                    switch($type)
                    {
                        case 'validate':
                            $error = self::processValidateTask($task);
                            break;
                        case 'install':
                            $error = self::processInstallTask($task);
                            break;
                        default:
                            $error = self::processReissueTask($task);
                    }
                }
                catch(\Throwable $e)
                {
                    $error = $e->getMessage();
                    self::log($task->client_id, $task->service_id, 'error', '[' . ucfirst($type) . '] Error: ' . $error);
                    AutoInstallQueue::markError($task->id, $error);
                }

                if($error)
                {
                    $lastError = $error;
                }
            }
        }

        try
        {
            self::scanAutoReissue();
        }
        catch(\Throwable $e)
        {
            $lastError = $e->getMessage();
        }

        return $lastError;
    }

    /**
     * Returns [$taskData, $state] or null when the task should not be processed anymore (it is closed here).
     */
    protected static function openTask($task)
    {
        $state = self::get($task->service_id);
        if(!$state || $state['status'] === self::STATUS_DISABLED)
        {
            AutoInstallQueue::markDone($task->id, 'AutoInstall is disabled for this service');
            return null;
        }

        $hosting = Capsule::table('tblhosting')->where('id', $task->service_id)->first();
        if(!$hosting || $hosting->domainstatus !== 'Active')
        {
            AutoInstallQueue::markDone($task->id, 'Service is not active');
            return null;
        }

        return [json_decode($task->data, true) ?: [], $state];
    }

    protected static function processValidateTask($task)
    {
        $opened = self::openTask($task);
        if($opened === null)
        {
            return '';
        }
        list($taskData, $state) = $opened;

        $serviceId  = (int)$task->service_id;
        $clientId   = (int)$task->client_id;
        $remoteId   = (int)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->value('remoteid');
        $apiOrderId = !empty($taskData['api_order_id']) ? (int)$taskData['api_order_id'] : $remoteId;

        if($apiOrderId <= 0)
        {
            throw new \Exception('Missing API order ID - task cannot be processed');
        }

        // The order was replaced (e.g. renewal created a new order) - this task is obsolete
        if(empty($taskData['is_reissue']) && $remoteId > 0 && $remoteId !== $apiOrderId)
        {
            AutoInstallQueue::markDone($task->id, 'Order replaced by #' . $remoteId);
            return '';
        }

        $api    = self::getApi($serviceId);
        $order  = $api->getOrder($apiOrderId, $serviceId);
        $status = isset($order['order']['status']) ? strtolower($order['order']['status']) : '';

        $taskData['validate_attempts'] = (isset($taskData['validate_attempts']) ? (int)$taskData['validate_attempts'] : 0) + 1;
        if(!isset($taskData['last_status']) || $taskData['last_status'] !== $status)
        {
            self::log($clientId, $serviceId, 'info', '[Validate] Order #' . $apiOrderId . ' status: ' . strtoupper($status ?: 'unknown'));
            $taskData['last_status'] = $status;
        }

        if($status === 'active')
        {
            $commonName = isset($order['orderData']['common_name']['name']) ? $order['orderData']['common_name']['name'] : $state['common_name'];
            $files      = $api->getCertificateFiles($apiOrderId);
            $cert       = self::extractCertificate($files, $commonName);

            if(empty($cert['crt']))
            {
                AutoInstallQueue::updateTaskData($task->id, $taskData);
                AutoInstallQueue::markPending($task->id, 'Certificate files not yet available');
                return '';
            }

            // Reissue / renewal still returns the previously installed certificate - wait for the new one
            if(!empty($state['installed_crt_hash']) && sha1(trim($cert['crt'])) === $state['installed_crt_hash'])
            {
                $taskData['same_cert_checks'] = (isset($taskData['same_cert_checks']) ? (int)$taskData['same_cert_checks'] : 0) + 1;
                AutoInstallQueue::updateTaskData($task->id, $taskData);

                if($taskData['same_cert_checks'] >= self::SAME_CERT_MAX_CHECKS)
                {
                    $message = 'The new certificate has not been issued - the API still returns the installed one.';
                    self::log($clientId, $serviceId, 'error', '[Validate] ' . $message);
                    AutoInstallQueue::markFailed($task->id, $message);
                    $state['last_error'] = $message;
                    self::save($serviceId, $state, self::STATUS_FAILED);
                    return $message;
                }

                AutoInstallQueue::markPending($task->id, 'Waiting for the new certificate');
                return '';
            }

            $validTill = isset($files['validity']['end']) ? $files['validity']['end'] : '';
            self::log($clientId, $serviceId, 'success', '[Validate] Certificate issued for domain: ' . $commonName .
                ($validTill ? ' | Valid till: ' . $validTill : ''));

            AutoInstallQueue::addTask($clientId, $serviceId, 'install', [
                'domain'       => $state['domain'],
                'api_order_id' => $apiOrderId,
                'is_renewal'   => !empty($taskData['is_renewal']),
                'is_reissue'   => !empty($taskData['is_reissue']),
            ]);
            self::log($clientId, $serviceId, 'info', '[Validate] Install task created - the certificate will be installed on cPanel automatically');

            AutoInstallQueue::updateTaskData($task->id, $taskData);
            AutoInstallQueue::markDone($task->id);
            return '';
        }

        if(in_array($status, self::FINAL_ORDER_STATUSES))
        {
            $message = 'Certificate order is ' . $status . ' - validation cannot proceed.';
            self::log($clientId, $serviceId, 'error', '[Validate] ' . $message);
            AutoInstallQueue::markFailed($task->id, $message);
            $state['last_error'] = $message;
            self::save($serviceId, $state, self::STATUS_FAILED);
            return $message;
        }

        $items   = isset($order['items']) && is_array($order['items']) ? $order['items'] : [];
        $item    = reset($items);
        $domains = isset($item['domains']) && is_array($item['domains']) ? $item['domains'] : [];

        $taskData = self::createDnsRecords($task, $taskData, $domains);
        $taskData = self::switchDnsToEmailIfNeeded($task, $taskData, $api, $apiOrderId, $order, $domains);
        $taskData = self::uploadValidationFiles($task, $taskData, $domains);

        // Validation file is on the server but the CA still did not verify it - advise the client to use email
        if(!empty($taskData['files_uploaded']))
        {
            $taskData['post_upload_checks'] = (isset($taskData['post_upload_checks']) ? (int)$taskData['post_upload_checks'] : 0) + 1;

            if($taskData['post_upload_checks'] >= self::VALIDATION_WARNING_AFTER_CHECKS && empty($taskData['validation_warning_sent']))
            {
                $method = 'http';
                foreach($domains as $domain)
                {
                    if(isset($domain['method']) && in_array(strtolower($domain['method']), ['http', 'https']))
                    {
                        $method = strtolower($domain['method']);
                        break;
                    }
                }

                if(AutoInstallEmailTemplates::sendValidationWarningEmail($serviceId, $state['domain'], $method))
                {
                    $taskData['validation_warning_sent'] = true;
                    self::log($clientId, $serviceId, 'info', '[Validate] Warning email sent to the client after ' . $taskData['post_upload_checks'] .
                        ' checks without CA verification. Client advised to switch to email validation.');
                }
            }
        }

        AutoInstallQueue::updateTaskData($task->id, $taskData);
        AutoInstallQueue::markPending($task->id, 'Awaiting domain validation (status: ' . $status . ')');

        return '';
    }

    protected static function processInstallTask($task)
    {
        $opened = self::openTask($task);
        if($opened === null)
        {
            return '';
        }
        list($taskData, $state) = $opened;

        $serviceId  = (int)$task->service_id;
        $clientId   = (int)$task->client_id;
        $domain     = $state['domain'];
        $apiOrderId = !empty($taskData['api_order_id']) ? (int)$taskData['api_order_id'] : (int)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->value('remoteid');

        try
        {
            $api        = self::getApi($serviceId);
            $order      = $api->getOrder($apiOrderId, $serviceId);
            $commonName = isset($order['orderData']['common_name']['name']) ? $order['orderData']['common_name']['name'] : $state['common_name'];
            $files      = $api->getCertificateFiles($apiOrderId);
            $cert       = self::extractCertificate($files, $commonName);

            if(empty($cert['crt']))
            {
                self::log($clientId, $serviceId, 'info', '[Install] Certificate data not yet available - will retry on the next cron run');
                AutoInstallQueue::markPending($task->id, 'Certificate not yet available');
                return '';
            }

            $privateKey = !empty($state['private_key']) ? \decrypt($state['private_key']) : '';
            if(empty($privateKey))
            {
                $message = 'Private key not found - the certificate has to be installed manually.';
                self::log($clientId, $serviceId, 'error', '[Install] ' . $message);
                AutoInstallQueue::markFailed($task->id, $message);
                $state['last_error'] = $message;
                self::save($serviceId, $state, self::STATUS_FAILED);
                return $message;
            }

            self::log($clientId, $serviceId, 'info', '[Install] Looking up cPanel hosting account for domain: ' . $domain);

            $cpanelService = Cpanel::getServiceByDomain($clientId, $domain);
            if($cpanelService === false)
            {
                throw new \Exception('No cPanel hosting account found for domain: ' . $domain .
                    ' (client #' . $clientId . '). Make sure the domain exists in a cPanel account owned by this client.');
            }

            $cpanel = new Cpanel();
            $cpanel->setService($cpanelService);
            $cpanel->installSSL($cpanelService->user, $domain, $cert['crt'], $privateKey, $cert['ca']);

            $items = isset($order['items']) && is_array($order['items']) ? $order['items'] : [];
            $item  = reset($items);

            $state['installed_crt_hash'] = sha1(trim($cert['crt']));
            $state['installed_at']       = date('Y-m-d H:i:s');
            $state['api_order_id']       = $apiOrderId;
            $state['valid_till']         = isset($files['validity']['end']) ? $files['validity']['end'] : '';
            $state['subscription_end']   = isset($item['subscription']['end']) ? $item['subscription']['end'] : '';
            $state['last_error']         = '';
            self::save($serviceId, $state, self::STATUS_INSTALLED);

            self::log($clientId, $serviceId, 'success', '[Install] SSL certificate installed successfully on cPanel - domain: ' . $domain .
                ' | server: ' . $cpanelService->hostname);

            $sent = !empty($taskData['is_renewal'])
                ? AutoInstallEmailTemplates::sendRenewalCompleteEmail($serviceId, $domain)
                : AutoInstallEmailTemplates::sendCertInstalledEmail($serviceId, $domain);
            if(!$sent)
            {
                self::log($clientId, $serviceId, 'error', '[Install] Failed to send the notification email to the client');
            }

            AutoInstallQueue::markDone($task->id);
            return '';
        }
        catch(\Exception $e)
        {
            $final = AutoInstallQueue::markError($task->id, $e->getMessage());
            self::log($clientId, $serviceId, 'error', '[Install] Error: ' . $e->getMessage() . ($final ? ' - giving up after ' . AutoInstallQueue::MAX_ATTEMPTS . ' attempts' : ''));

            $state['last_error'] = $e->getMessage();
            self::save($serviceId, $state, $final ? self::STATUS_FAILED : $state['status']);

            return $e->getMessage();
        }
    }

    protected static function processReissueTask($task)
    {
        $opened = self::openTask($task);
        if($opened === null)
        {
            return '';
        }
        list($taskData, $state) = $opened;

        $serviceId = (int)$task->service_id;
        $clientId  = (int)$task->client_id;
        $remoteId  = (int)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->value('remoteid');

        try
        {
            if($remoteId <= 0)
            {
                throw new \Exception('Missing API order ID');
            }

            self::log($clientId, $serviceId, 'info', '[Reissue] Starting automatic reissue for domain: ' . $state['domain'] . ' (API order #' . $remoteId . ')');

            $api          = self::getApi($serviceId);
            $order        = $api->getOrder($remoteId, $serviceId);
            $requestOrder = isset($order['orderData']) && is_array($order['orderData']) ? $order['orderData'] : [];

            if(empty($requestOrder['csr']) || empty($requestOrder['common_name']))
            {
                throw new \Exception('CSR not found for service #' . $serviceId . ' - cannot reissue automatically');
            }

            $alternativeNames = isset($requestOrder['alternative_names']) && is_array($requestOrder['alternative_names']) ? $requestOrder['alternative_names'] : [];

            // Same CSR = same private key, so the reissued certificate can be installed automatically
            $reissue = $api->reissueCertificate($remoteId, [
                'csr'                         => trim($requestOrder['csr']),
                'common_name'                 => $requestOrder['common_name'],
                'existing_alternative_names'  => $alternativeNames,
                'submitted_alternative_names' => $alternativeNames,
            ]);

            // api_order_id holds the certificate (item) ID - the one used by /certificates/* endpoints
            $newOrderId = SSLTrustCenterApi::getItemId($reissue, $remoteId);

            // Same bookkeeping as the client area reissue (home::reissueStep3)
            Request::where('serviceid', $serviceId)->where('name', 'addOrder')->update([
                'status'     => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $orderData  = $api->getOrder($newOrderId, $serviceId);
            $orderFiles = [];
            try
            {
                $orderFiles = $api->getCertificateFiles($newOrderId);
            }
            catch(\Exception $exception)
            {
            }
            Request::where('serviceid', $serviceId)->where('name', 'certificate')->update([
                'request'    => \encrypt(json_encode(['orderData' => $orderData, 'orderFiles' => $orderFiles])),
                'status'     => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $validateTask = [
                'domain'       => $state['domain'],
                'api_order_id' => $newOrderId,
                'is_reissue'   => true,
                'is_wildcard'  => !empty($state['is_wildcard']),
            ];
            if(isset($state['dcv_method']) && $state['dcv_method'] === 'dns')
            {
                $validateTask['dns_check_start'] = time();
            }
            if(!empty($state['dns_fallback_email']))
            {
                $validateTask['dns_fallback_email'] = $state['dns_fallback_email'];
            }
            AutoInstallQueue::addTask($clientId, $serviceId, 'validate', $validateTask);

            $state['last_error'] = '';
            self::save($serviceId, $state, self::STATUS_AWAITING);

            self::log($clientId, $serviceId, 'success', '[Reissue] Reissue submitted successfully - validation and installation tasks queued');
            AutoInstallQueue::markDone($task->id);

            return '';
        }
        catch(\Exception $e)
        {
            $final = AutoInstallQueue::markError($task->id, $e->getMessage());
            self::log($clientId, $serviceId, 'error', '[Reissue] Error: ' . $e->getMessage());

            if($final)
            {
                $validTill = isset($taskData['cert_expiry']) ? $taskData['cert_expiry'] : (isset($state['valid_till']) ? $state['valid_till'] : '');
                $daysLeft  = $validTill ? (int)floor((strtotime($validTill) - time()) / 86400) : 0;

                if(!AutoInstallEmailTemplates::sendAutoReissueFailedEmail($serviceId, $state['domain'], $validTill, $daysLeft, $e->getMessage()))
                {
                    self::log($clientId, $serviceId, 'error', '[Reissue] Failed to send the reissue-failed notification email to the client');
                }
            }

            return $e->getMessage();
        }
    }

    /**
     * Queues a reissue for installed certificates that expire soon while the subscription is still running.
     */
    public static function getWildcardDnsTimeoutMinutes()
    {
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $minutes     = isset($addonConfig['sslSettings']['wildcardDnsTimeoutMinutes']) ? (int)$addonConfig['sslSettings']['wildcardDnsTimeoutMinutes'] : 0;

        return $minutes > 0 ? $minutes : self::DEFAULT_WILDCARD_DNS_TIMEOUT_MINUTES;
    }

    public static function getAutoReissueDaysBeforeExpire()
    {
        $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();
        $sslSettings = isset($addonConfig['sslSettings']) && is_array($addonConfig['sslSettings']) ? $addonConfig['sslSettings'] : [];

        if(!isset($sslSettings['autoReissueDaysBeforeExpire']) || $sslSettings['autoReissueDaysBeforeExpire'] === '')
        {
            return self::DEFAULT_AUTO_REISSUE_DAYS_BEFORE_EXPIRE;
        }

        return max(0, (int)$sslSettings['autoReissueDaysBeforeExpire']);
    }

    /**
     * The certificate of this service is reissued automatically - the client does not need the manual reissue reminder.
     */
    public static function isAutoReissueActive($serviceId)
    {
        if(self::getAutoReissueDaysBeforeExpire() <= 0)
        {
            return false;
        }

        $row = Request::where('serviceid', $serviceId)->where('name', self::REQUEST_NAME)->first();

        return $row && in_array($row->status, [self::STATUS_AWAITING, self::STATUS_INSTALLED]);
    }

    protected static function scanAutoReissue()
    {
        $daysBeforeExpire = self::getAutoReissueDaysBeforeExpire();
        if($daysBeforeExpire <= 0)
        {
            return;
        }

        $rows = Request::where('name', self::REQUEST_NAME)->where('status', self::STATUS_INSTALLED)->get();

        foreach($rows as $row)
        {
            $serviceId = (int)$row->serviceid;
            $clientId  = 0;

            try
            {
                $hosting = Capsule::table('tblhosting')->where('id', $serviceId)->first();
                if(!$hosting || $hosting->domainstatus !== 'Active')
                {
                    continue;
                }
                $clientId = (int)$hosting->userid;

                $state     = self::get($serviceId);
                $validTill = isset($state['valid_till']) ? trim((string)$state['valid_till']) : '';
                $validTs   = $validTill ? strtotime($validTill) : false;
                if(!$validTs)
                {
                    continue;
                }

                $daysLeft = (int)floor(($validTs - time()) / 86400);
                if($daysLeft > $daysBeforeExpire)
                {
                    continue;
                }

                // End of the subscription - a renewal (new order) is needed, not a reissue
                $subscriptionEnd = !empty($state['subscription_end']) ? strtotime($state['subscription_end']) : false;
                if(!$subscriptionEnd || $subscriptionEnd <= $validTs + 86400)
                {
                    continue;
                }

                if(isset($state['reissue_for_valid_till']) && $state['reissue_for_valid_till'] === $validTill)
                {
                    continue;
                }

                $remoteId = (int)Capsule::table('tblsslorders')->where('serviceid', $serviceId)->value('remoteid');
                if($remoteId <= 0 || (!empty($state['api_order_id']) && (int)$state['api_order_id'] !== $remoteId))
                {
                    continue;
                }

                if(AutoInstallQueue::hasOpenTask($serviceId))
                {
                    continue;
                }

                AutoInstallQueue::addTask($clientId, $serviceId, 'reissue', [
                    'domain'       => $state['domain'],
                    'api_order_id' => $remoteId,
                    'cert_expiry'  => $validTill,
                ]);

                $state['reissue_for_valid_till'] = $validTill;
                self::save($serviceId, $state, self::STATUS_INSTALLED);

                self::log($clientId, $serviceId, 'info', '[AutoReissue] Reissue task created for domain: ' . $state['domain'] .
                    ' - certificate expires ' . $validTill . ' (' . $daysLeft . ' days remaining)');
            }
            catch(\Exception $e)
            {
                self::log($clientId, $serviceId, 'error', '[AutoReissue] Error checking service #' . $serviceId . ': ' . $e->getMessage());
            }
        }
    }

    // ------------------------------------------------------------------ //
    // Validation helpers
    // ------------------------------------------------------------------ //

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

    public static function getApi($serviceId)
    {
        $packageId = Capsule::table('tblhosting')->where('id', $serviceId)->value('packageid');
        if(!$packageId)
        {
            throw new \Exception('Service #' . $serviceId . ' not found');
        }

        $configProduct = (new ProductRepository())->getProductConfiguration($packageId);
        $addonConfig   = (new AddonModuleRepository())->getModuleConfiguration();

        $provider    = strtolower($configProduct['provider']);
        $credentials = $addonConfig['credentials'][$provider];

        if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
        {
            $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
            $credentials['AuthToken']   = $credentials['TestAuthToken'];
        }

        return new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
    }

    /**
     * Certificate + CA bundle from the API files response (/certificates/{category}/{id}/files).
     * Files are recognized by their content, not by the name - the API names the domain certificate
     * by its ID (e.g. "3438104620.crt") and every intermediate / root separately.
     *  - crt: end-entity certificate (not a CA), preferably the one issued for $commonName
     *  - ca:  intermediates from the certificate issuer up to the root (the self-signed root is skipped)
     */
    public static function extractCertificate($orderFiles, $commonName)
    {
        $files = isset($orderFiles['files']) && is_array($orderFiles['files']) ? $orderFiles['files'] : [];
        $certs = [];

        foreach($files as $file)
        {
            if(!isset($file['name'], $file['content']) || substr(strtolower($file['name']), -4) !== '.crt')
            {
                continue;
            }

            $parsed = openssl_x509_parse($file['content']);
            if(!$parsed)
            {
                continue;
            }

            $certs[] = [
                'content'    => trim($file['content']) . "\n",
                'subject'    => self::certificateName($parsed['subject']),
                'issuer'     => self::certificateName($parsed['issuer']),
                'isCa'       => isset($parsed['extensions']['basicConstraints']) && stripos($parsed['extensions']['basicConstraints'], 'CA:TRUE') !== false,
                'selfSigned' => self::certificateName($parsed['subject']) === self::certificateName($parsed['issuer']),
                'names'      => self::certificateDomains($parsed),
            ];
        }

        // End-entity certificate - the one issued for the common name first
        $leaf       = null;
        $commonName = strtolower(trim((string)$commonName));
        foreach($certs as $cert)
        {
            if(!$cert['isCa'] && ($leaf === null || in_array($commonName, $cert['names'], true)))
            {
                $leaf = $cert;
            }
        }

        if($leaf === null)
        {
            return ['crt' => '', 'ca' => ''];
        }

        // CA bundle - follow the issuers from the certificate up to the root
        $ca     = '';
        $issuer = $leaf['issuer'];
        for($i = 0; $i < 10; $i++)
        {
            $next = null;
            foreach($certs as $cert)
            {
                if($cert['isCa'] && $cert['subject'] === $issuer)
                {
                    $next = $cert;
                    break;
                }
            }
            if($next === null || $next['selfSigned'])
            {
                break;
            }
            $ca    .= $next['content'];
            $issuer = $next['issuer'];
        }

        return ['crt' => $leaf['content'], 'ca' => $ca];
    }

    protected static function certificateName(array $name)
    {
        ksort($name);

        return json_encode($name);
    }

    protected static function certificateDomains(array $parsed)
    {
        $names = isset($parsed['subject']['CN']) ? [strtolower((string)$parsed['subject']['CN'])] : [];

        if(!empty($parsed['extensions']['subjectAltName']))
        {
            foreach(explode(',', $parsed['extensions']['subjectAltName']) as $san)
            {
                $san = trim($san);
                if(stripos($san, 'DNS:') === 0)
                {
                    $names[] = strtolower(trim(substr($san, 4)));
                }
            }
        }

        return $names;
    }
}
