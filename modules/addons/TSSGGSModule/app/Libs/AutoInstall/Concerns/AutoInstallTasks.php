<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\Concerns;

use ModulesGarden\TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\Billing\SanUpgrade;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\FreeName;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallReport;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\Cpanel;

/**
 * The AutoInstall queue: picking tasks and running validate / install / reissue tasks.
 * Part of AutoInstallHelpers (used there as a trait).
 */
trait AutoInstallTasks
{
    /**
     * Processes the whole AutoInstall queue. Returns the last error message (for CronCheck) or an empty string.
     */
    public static function processQueue()
    {
        // The queue can be started by more than one cron run at a time (SSLAutoInstall) - only one run at a time,
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
            // a product without SAN support at TheSSLStore: the free www / base-domain name is added by the vendor and
            // must not be sent (FreeName; an order stored before 1.15.0 may still list it)
            if(FreeName::addedForService($serviceId))
            {
                $alternativeNames = FreeName::strip($alternativeNames, (string)($requestOrder['common_name']['name'] ?? ''));
            }

            // Same CSR = same private key, so the reissued certificate can be installed automatically
            $reissue = $api->reissueCertificate($remoteId, [
                'csr'                         => trim($requestOrder['csr']),
                'common_name'                 => $requestOrder['common_name'],
                'existing_alternative_names'  => $alternativeNames,
                'submitted_alternative_names' => $alternativeNames,
            ]);

            // api_order_id holds the certificate (item) ID - the one used by /certificates/* endpoints
            $newOrderId = SSLTrustCenterApi::getItemId($reissue, $remoteId);
            // SANs bought before a reissue can no longer be given back for a refund
            SanUpgrade::noteReissue($serviceId);

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
                AutoInstallReport::finish($serviceId, AutoInstallReport::FAILED, 'Automatic reissue: ' . $e->getMessage());
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
}
