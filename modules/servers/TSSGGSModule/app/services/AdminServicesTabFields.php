<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Admin service tab: order IDs, vendor status, validation per domain, dates, certificate, install target.
 * Validation method, install target and a reissue with a new CSR can be changed from the tab (save()).
 */
class AdminServicesTabFields
{
    const DCV_METHODS = ['email', 'http', 'https', 'dns'];

    public function run($params)
    {
        try
        {
            $sslService = SSL::getByServiceId($params['serviceid']);
            if(is_null($sslService))
            {
                throw new \Exception('The create action has not been initialized');
            }

            if($sslService->status === 'Awaiting Configuration')
            {
                return ['Configuration Status' => '<span class="label label-warning">Awaiting Configuration</span>'];
            }

            if(empty($sslService->remoteid))
            {
                throw new \Exception('The id not exist in this SSL order');
            }

            if(CertificateService::category($params['serviceid']) !== CertificateService::CATEGORY_TLS)
            {
                return ['Gateway Item ID' => (int)$sslService->remoteid];
            }

            return array_merge($this->getCertificateDetails($params, $sslService), $this->getAutoInstallDetails($params['serviceid']));
        }
        catch(\Exception $ex)
        {
            return ['Error' => htmlspecialchars($ex->getMessage())];
        }
    }

    private function getCertificateDetails($params, $sslService)
    {
        $serviceId = (int)$params['serviceid'];
        $stored    = CertificateService::stored($serviceId);
        $order     = isset($stored['orderData']) ? $stored['orderData'] : [];
        $items     = isset($order['items']) ? (array)$order['items'] : [];
        $item      = (array)reset($items);
        $status    = isset($order['order']['status']) ? (string)$order['order']['status'] : '';
        $labels    = ['active' => 'label-success', 'processing' => 'label-info', 'pending' => 'label-info', 'cancelled' => 'label-danger',
                      'rejected' => 'label-danger', 'revoked' => 'label-danger', 'expired' => 'label-default'];
        $synced    = Request::where('serviceid', $serviceId)->where('name', 'certificate')->value('updated_at');

        $fields = [];
        $fields['Configuration Status'] = htmlspecialchars($sslService->status);
        $fields['Gateway Order']        = 'Order #' . (int)($order['order']['id'] ?? 0) . ' | Item #' . (int)$sslService->remoteid
            . (!empty($item['vendor']['id']) ? ' | Vendor (CA) order #' . htmlspecialchars($item['vendor']['id']) : '')
            . ' | ' . htmlspecialchars(Helpers::vendorToDisplay($params['configoption1']));
        $fields['Vendor Status']        = '<span class="label ' . ($labels[$status] ?? 'label-default') . '">' . htmlspecialchars(ucfirst($status ?: 'unknown')) . '</span>'
            . (!empty($order['order']['date']) ? ' &nbsp;ordered ' . htmlspecialchars($order['order']['date']) : '')
            . ($synced ? ' &nbsp;| last synchronized ' . htmlspecialchars($synced) : '');

        $validity = isset($stored['orderFiles']['validity']) ? $stored['orderFiles']['validity'] : [];
        if(!empty($validity['begin']) || !empty($item['subscription']['end']))
        {
            $fields['Validity'] = (!empty($validity['begin']) ? 'Certificate ' . htmlspecialchars(substr($validity['begin'], 0, 10)) . ' - ' . htmlspecialchars(substr((string)($validity['end'] ?? ''), 0, 10)) : '')
                . (!empty($item['subscription']['end']) ? ' &nbsp;| Subscription ' . htmlspecialchars(substr((string)$item['subscription']['begin'], 0, 10)) . ' - ' . htmlspecialchars(substr($item['subscription']['end'], 0, 10)) : '');
        }

        $fields['Domain Validation'] = $this->validationTable($params, $item, $status);

        if($status === 'active')
        {
            try
            {
                $bundle                = CertificateService::bundle($serviceId);
                $fields['Certificate'] = '<details><summary style="cursor:pointer;">View CRT / CA bundle</summary>'
                    . '<textarea readonly rows="8" class="form-control" style="font-family:monospace;font-size:11px;margin-top:6px;">' . htmlspecialchars($bundle->crt()) . '</textarea>'
                    . ($bundle->ca() !== '' ? '<textarea readonly rows="8" class="form-control" style="font-family:monospace;font-size:11px;margin-top:6px;">' . htmlspecialchars($bundle->ca()) . '</textarea>' : '')
                    . '</details>';
            }
            catch(\Exception $e)
            {
            }

            $fields['Reissue'] = '<textarea name="tssggs_reissue_csr" rows="4" class="form-control" style="font-family:monospace;font-size:11px;" placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>'
                . '<small class="text-muted">Paste a new CSR and save to reissue the certificate with the current domains and validation methods.</small>';
        }

        return $fields;
    }

    /**
     * Domains with their method and state; pending domains get a method select + approver email input
     */
    private function validationTable($params, array $item, $status)
    {
        $allowed = array_values(array_intersect(self::DCV_METHODS, array_map(function($m) {
            return in_array($m, ['dns-cname', 'dns-txt'], true) ? 'dns' : $m;
        }, explode(',', strtolower((string)$params['configoption5'])))));
        $allowed = $allowed ?: self::DCV_METHODS;

        $html = '<table class="table table-condensed" style="margin:0;max-width:760px;"><tr><th>Domain</th><th>Method</th><th>Status</th></tr>';
        foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
        {
            $name     = (string)($domain['name'] ?? '');
            $method   = (string)($domain['method'] ?? '');
            $approved = !empty($domain['approved']);
            $isEmail  = strpos($method, '@') !== false || $method === 'email';

            $methodCell = htmlspecialchars($isEmail ? 'email' . ($method !== 'email' ? ' (' . $method . ')' : '') : $method);
            if(!$approved && in_array($status, ['pending', 'processing'], true))
            {
                $key        = htmlspecialchars($name);
                $methodCell = '<select name="tssggs_dcv[' . $key . ']" class="form-control input-sm" style="display:inline-block;width:auto;"><option value="">' . $methodCell . '</option>';
                foreach($allowed as $option)
                {
                    if(strpos($name, '*.') === 0 && in_array($option, ['http', 'https'], true))
                    {
                        continue;
                    }
                    $methodCell .= '<option value="' . $option . '">change to ' . $option . '</option>';
                }
                $methodCell .= '</select> <input name="tssggs_approver[' . $key . ']" class="form-control input-sm" style="display:inline-block;width:200px;" placeholder="approver email (email method)">';
            }

            $html .= '<tr><td>' . htmlspecialchars($name) . '</td><td>' . $methodCell . '</td><td>'
                . ($approved ? '<span class="label label-success">Validated</span>' : '<span class="label label-warning">Pending</span>') . '</td></tr>';
        }

        return $html . '</table>';
    }

    private function getAutoInstallDetails($serviceId)
    {
        try
        {
            $autoInstall = AutoInstallHelpers::getStatusForView($serviceId, 20);
        }
        catch(\Exception $ex)
        {
            return [];
        }

        $target = '<input name="tssggs_install_domain" class="form-control input-sm" style="display:inline-block;width:260px;" value="' . htmlspecialchars($autoInstall ? $autoInstall['domain'] : '') . '" placeholder="domain on the client\'s cPanel account"' . ($autoInstall ? '' : ' disabled') . '>'
            . ' <small class="text-muted">' . ($autoInstall
                ? 'Domain the certificate is installed on (AutoInstall). Changing it re-queues the installation.'
                : 'The client can install the issued certificate with "Install on server" in the client area.') . '</small>';

        if(!$autoInstall)
        {
            return ['SSL Install Type' => 'Manual', 'Install Target' => $target];
        }

        $labels = [
            AutoInstallHelpers::STATUS_AWAITING  => 'label-info',
            AutoInstallHelpers::STATUS_INSTALLED => 'label-success',
            AutoInstallHelpers::STATUS_FAILED    => 'label-danger',
            AutoInstallHelpers::STATUS_DISABLED  => 'label-warning',
        ];
        $label = isset($labels[$autoInstall['status']]) ? $labels[$autoInstall['status']] : 'label-default';

        $status = '<span class="label ' . $label . '">' . htmlspecialchars(ucwords(str_replace('_', ' ', $autoInstall['status']))) . '</span>'
            . ' on <strong>' . htmlspecialchars($autoInstall['domain']) . '</strong>';
        if($autoInstall['installed_at'])
        {
            $status .= ' | installed: ' . htmlspecialchars($autoInstall['installed_at']);
        }
        if($autoInstall['valid_till'])
        {
            $status .= ' | valid till: ' . htmlspecialchars($autoInstall['valid_till']);
        }
        if($autoInstall['last_error'])
        {
            $status .= '<br><span style="color:#c0392b;">Last error: ' . htmlspecialchars($autoInstall['last_error']) . '</span>';
        }

        $return = ['SSL Install Type' => 'AutoInstall (cPanel)', 'AutoInstall Status' => $status, 'Install Target' => $target];

        if(!empty($autoInstall['logs']))
        {
            $logs = '<div style="max-height:220px;overflow:auto;font-size:11px;">';
            foreach($autoInstall['logs'] as $log)
            {
                $color = $log['type'] === 'error' ? '#c0392b' : ($log['type'] === 'success' ? '#27ae60' : '#333');
                $logs  .= '<div style="color:' . $color . ';">' . htmlspecialchars($log['date']) . ' - ' . htmlspecialchars($log['message']) . '</div>';
            }
            $logs                     .= '</div>';
            $return['Certificate Log'] = $logs;
        }

        return $return;
    }

    /**
     * AdminServicesTabFieldsSave - validation method changes, install target, reissue with a new CSR
     */
    public function save($params)
    {
        $serviceId = (int)$params['serviceid'];

        try
        {
            $methods = [];
            foreach(isset($_POST['tssggs_dcv']) && is_array($_POST['tssggs_dcv']) ? $_POST['tssggs_dcv'] : [] as $domain => $method)
            {
                if(!in_array($method, self::DCV_METHODS, true))
                {
                    continue;
                }
                if($method === 'email')
                {
                    $email = trim((string)($_POST['tssggs_approver'][$domain] ?? ''));
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        throw new \Exception('Approver email for ' . $domain . ' is missing or not valid');
                    }
                    $method = $email;
                }
                $methods[$domain] = $method;
            }
            if($methods)
            {
                CertificateService::changeValidation($serviceId, $methods);
            }

            $installDomain = strtolower(trim((string)($_POST['tssggs_install_domain'] ?? '')));
            $state         = AutoInstallHelpers::get($serviceId);
            if($installDomain !== '' && $state && $installDomain !== $state['domain'])
            {
                $state['domain'] = $installDomain;
                $active          = $state['status'] !== AutoInstallHelpers::STATUS_DISABLED;
                AutoInstallHelpers::save($serviceId, $state, $active ? AutoInstallHelpers::STATUS_AWAITING : AutoInstallHelpers::STATUS_DISABLED);
                if($active && CertificateService::status($serviceId) === 'active')
                {
                    AutoInstallQueue::addTask((int)$params['userid'], $serviceId, 'install', ['api_order_id' => (int)SSL::getByServiceId($serviceId)->remoteid]);
                }
                CertificateService::event($serviceId, 'info', 'Install target changed to ' . $installDomain . ' by the administrator');
            }

            $csr = trim((string)($_POST['tssggs_reissue_csr'] ?? ''));
            if($csr !== '')
            {
                CertificateService::reissue($serviceId, $csr, $this->currentMethods($serviceId, $csr));
            }
        }
        catch(\Throwable $e)
        {
            CertificateService::event($serviceId, 'error', 'Admin change failed: ' . $e->getMessage());
        }
    }

    /**
     * Current validation methods for the domains of the new CSR (common name of the CSR + current SANs)
     */
    private function currentMethods($serviceId, $csr)
    {
        $stored  = CertificateService::stored($serviceId);
        $order   = $stored['orderData']['orderData'] ?? [];
        $cn      = openssl_csr_get_subject($csr)['CN'] ?? '';
        $default = $order['common_name']['validation_method'] ?? 'dns';

        $methods = [$cn => $default];
        foreach(isset($order['alternative_names']) ? (array)$order['alternative_names'] : [] as $san)
        {
            if(!empty($san['name']) && $san['name'] !== $cn)
            {
                $methods[$san['name']] = $san['validation_method'] ?? $default;
            }
        }

        return $methods;
    }
}
