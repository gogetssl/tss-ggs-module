<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\App\Libs\AdminText;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallHelpers;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Cpanel;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Admin service tab: order IDs, vendor status, validation per domain, dates, certificate, install target.
 * Validation method, install target and a reissue with a new CSR can be changed from the tab (save()).
 */
class AdminServicesTabFields
{
    const DCV_METHODS = ['email', 'http', 'https', 'dns'];

    /** admin text as HTML (langs/admin/<language>.php, see App\Libs\AdminText) */
    private static function t($key, $english, array $replace = [])
    {
        return AdminText::html($key, $english, $replace);
    }

    /** "Order #1 | Item #2 | Vendor (CA) order #3" line of the gateway / vendor order */
    private static function orderLine($orderId, $itemId, $vendorId = '', $itemKey = 'item_number', $itemEnglish = 'Item #:id:', $vendorKey = 'vendor_order_number', $vendorEnglish = 'Vendor (CA) order #:id:')
    {
        return self::t('order_number', 'Order #:id:', ['id' => (int)$orderId]) . ' | ' . self::t($itemKey, $itemEnglish, ['id' => (int)$itemId])
            . (!empty($vendorId) ? ' | ' . self::t($vendorKey, $vendorEnglish, ['id' => $vendorId]) : '');
    }

    /** status label + "ordered ..." + "last refresh ..." */
    private static function vendorStatus($status, array $labels, $orderedAt, $refreshedAt, $extra = '')
    {
        return '<span class="label ' . ($labels[$status] ?? 'label-default') . '">' . htmlspecialchars(ucfirst($status ?: AdminText::get('unknown', 'unknown'))) . '</span>'
            . $extra
            . (!empty($orderedAt) ? ' &nbsp;' . self::t('ordered_at', 'ordered :date:', ['date' => $orderedAt]) : '')
            . (!empty($refreshedAt) ? ' &nbsp;<small class="text-muted">' . self::t('last_refresh', 'last refresh :date:', ['date' => $refreshedAt]) . '</small>' : '');
    }

    public function run($params)
    {
        try
        {
            $sslService = SSL::getByServiceId($params['serviceid']);
            if(is_null($sslService))
            {
                throw new \Exception(AdminText::get('error_not_initialized', 'The create action has not been initialized'));
            }

            if($sslService->status === 'Awaiting Configuration')
            {
                $fields = [AdminText::get('configuration_status', 'Configuration Status') => '<span class="label label-warning">' . self::t('status_awaiting_configuration', 'Awaiting Configuration') . '</span>'];
                // S/MIME after a renewal: the orders placed before
                $gatewayType = \ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService::typeOfService((int)$params['serviceid']);
                if($gatewayType)
                {
                    $fields += $this->getSmimePreviousOrders($gatewayType::stored((int)$params['serviceid']));
                }

                return $fields;
            }

            if(empty($sslService->remoteid))
            {
                throw new \Exception(AdminText::get('error_no_remote_id', 'The SSL order has no %%VENDOR%% item ID yet'));
            }

            if(CertificateService::category($params['serviceid']) === CertificateService::CATEGORY_SMIME)
            {
                return $this->getSmimeDetails((int)$params['serviceid'], $sslService);
            }
            if(CertificateService::category($params['serviceid']) === CertificateService::CATEGORY_CS)
            {
                return $this->getCodeSigningDetails((int)$params['serviceid'], $sslService);
            }
            if(CertificateService::category($params['serviceid']) === CertificateService::CATEGORY_CODE_GUARD)
            {
                return $this->getCodeGuardDetails((int)$params['serviceid'], $sslService);
            }
            if(CertificateService::category($params['serviceid']) === CertificateService::CATEGORY_DS)
            {
                return $this->getDocumentSigningDetails((int)$params['serviceid'], $sslService);
            }
            if(CertificateService::category($params['serviceid']) !== CertificateService::CATEGORY_TLS)
            {
                return [AdminText::get('vendor_item_id', '%%VENDOR%% Item ID') => (int)$sslService->remoteid];
            }

            $fields = array_merge($this->getCertificateDetails($params, $sslService), $this->getAutoInstallDetails($params['serviceid'], (int)$params['userid']));
            $caa    = $this->caaWarning((int)$params['serviceid']);
            if($caa !== '')
            {
                // first, so the admin sees why the order does not move
                $fields = [AdminText::get('caa_record', 'CAA Record') => $caa] + $fields;
            }
            if(CertificateService::isSectigo($params['packageid']))
            {
                $override = CertificateService::renewalDaysOverride($params['serviceid']);
                $fields[AdminText::get('renewal_additional_days', 'Renewal Additional Days')] = '<input name="tssggs_renewal_days" class="form-control input-sm" style="display:inline-block;width:80px;" value="' . ($override === null ? '' : (int)$override) . '" placeholder="' . self::t('renewal_days_auto', 'auto') . '">'
                    . ' <small class="text-muted">' . self::t('renewal_days_hint', '0-:max: days carried over to the renewal order (Sectigo). Empty = the global setting, or the days left on the certificate.', ['max' => CertificateService::RENEWAL_DAYS_MAX]) . '</small>';
            }

            return $fields;
        }
        catch(\Exception $ex)
        {
            return [AdminText::get('error', 'Error') => htmlspecialchars($ex->getMessage())];
        }
    }

    /** CAA warning of a pending TLS certificate (App\Libs\CaaCheck), '' when nothing blocks it */
    private function caaWarning($serviceId)
    {
        try
        {
            $stored  = CertificateService::stored($serviceId);
            $waiting = [];
            foreach((array)($stored['orderData']['items'][0]['domains'] ?? []) as $domain)
            {
                if(empty($domain['approved']) && !empty($domain['name']))
                {
                    $waiting[] = (string)$domain['name'];
                }
            }
            $warning = \ModulesGarden\TSSGGSModule\App\Libs\CaaCheck::forService($serviceId, $waiting);

            return $warning ? \ModulesGarden\TSSGGSModule\App\Libs\CaaCheck::html($warning, [
                'title'       => AdminText::get('caa_title', 'A CAA DNS record does not allow this certificate'),
                'intro'       => AdminText::get('caa_intro', '%s will not issue for these domains: their CAA DNS record allows other certificate authorities only. The client sees the same notice.'),
                'inherited'   => AdminText::get('caa_inherited', 'record set on %s'),
                'now'         => AdminText::get('caa_now', 'CAA record now'),
                'add'         => AdminText::get('caa_add', 'Record to add'),
                'fix'         => AdminText::get('caa_fix', 'The domain owner adds the record (or removes the CAA records); the CA then issues without a new order.'),
                'gateway'     => AdminText::get('caa_gateway', 'The certificate authority reports that its CAA check failed for this order (the client sees the same notice).'),
                'gateway_fix' => AdminText::get('caa_gateway_fix', 'Fix: the domain owner adds %s to the domain\'s DNS (or removes its CAA records); the CA then issues without a new order.'),
            ]) : '';
        }
        catch(\Throwable $e)
        {
            return '';
        }
    }

    /**
     * S/MIME order on the module tab: status, gateway / CA order, email addresses and their confirmation, validity
     */
    private function getSmimeDetails($serviceId, $sslService)
    {
        $stored = \ModulesGarden\TSSGGSModule\App\Libs\SmimeService::stored($serviceId);
        $status = (string)($stored['status'] ?? '');
        $labels = ['active' => 'label-success', 'processing' => 'label-info', 'pending' => 'label-info', 'cancelled' => 'label-danger',
                   'rejected' => 'label-danger', 'failed' => 'label-danger', 'revoked' => 'label-danger', 'expired' => 'label-default'];

        $emails = [];
        foreach((array)($stored['emails'] ?? []) as $entry)
        {
            $confirmed = !empty($entry['approved']) || $status === 'active';
            $emails[]  = htmlspecialchars($entry['email']) . ' <span class="label ' . ($confirmed ? 'label-success' : 'label-warning') . '">' . ($confirmed ? self::t('email_confirmed', 'confirmed') : self::t('email_not_confirmed', 'not confirmed')) . '</span>';
        }

        $fields = [];
        $fields[AdminText::get('configuration_status', 'Configuration Status')] = htmlspecialchars($sslService->status);
        $fields[AdminText::get('vendor_order', '%%VENDOR%% Order')] = self::orderLine($stored['orderId'] ?? 0, $stored['itemId'] ?? 0, $stored['vendorOrderId'] ?? '');
        $fields[AdminText::get('vendor_status', 'Vendor Status')]   = self::vendorStatus($status, $labels, $stored['orderedAt'] ?? '', $stored['refreshedAt'] ?? '');
        $fields[AdminText::get('email_addresses', 'Email Addresses')] = $emails ? implode('<br>', $emails) : '-';
        $fields[AdminText::get('validity', 'Validity')] = (!empty($stored['validFrom']) ? htmlspecialchars(substr($stored['validFrom'], 0, 10)) : '-') . ' &rarr; '
            . (!empty($stored['validTill']) ? htmlspecialchars(substr($stored['validTill'], 0, 10)) : '-');
        $fields[AdminText::get('csr', 'CSR')] = !empty($stored['csrProvided']) ? self::t('csr_sent', 'Sent by the client') : self::t('csr_not_sent', 'Not sent: the certificate authority emails the client how to collect the certificate');
        if(!empty($stored['enrollmentLink']) && empty($stored['emails']))
        {
            $fields[AdminText::get('enrolment', 'Enrolment')] = self::t('enrolment_pending', 'Migrated invite order: the client has not enrolled yet.')
                . ' <a href="' . htmlspecialchars($stored['enrollmentLink']) . '" target="_blank" rel="noopener">' . self::t('enrolment_link', 'Enrolment link') . '</a>';
        }
        if(!empty($stored['migrated']))
        {
            $fields[AdminText::get('migrated', 'Migrated')] = self::t('migrated_yes', 'Yes, from the legacy module');
        }

        return $fields + $this->getSmimePreviousOrders($stored);
    }

    /**
     * Document signing order on the module tab: status, gateway / CA order, organization, delivery, validity
     */
    private function getDocumentSigningDetails($serviceId, $sslService)
    {
        $ds     = \ModulesGarden\TSSGGSModule\App\Libs\DocumentSigningService::class;
        $stored = $ds::stored($serviceId);
        $status = (string)($stored['status'] ?? '');
        $labels = ['active' => 'label-success', 'processing' => 'label-info', 'pending' => 'label-info', 'invited' => 'label-warning',
                   'cancelled' => 'label-danger', 'rejected' => 'label-danger', 'failed' => 'label-danger', 'revoked' => 'label-danger', 'expired' => 'label-default'];
        $model  = strtoupper((string)($stored['tokenModel'] ?? ''));

        $fields = [];
        $fields[AdminText::get('configuration_status', 'Configuration Status')] = htmlspecialchars($sslService->status);
        $fields[AdminText::get('vendor_order', '%%VENDOR%% Order')] = self::orderLine($stored['orderId'] ?? 0, $stored['itemId'] ?? 0, $stored['vendorOrderId'] ?? '');
        $fields[AdminText::get('vendor_status', 'Vendor Status')]   = self::vendorStatus($status, $labels, $stored['orderedAt'] ?? '', $stored['refreshedAt'] ?? '');
        $fields[AdminText::get('organization', 'Organization')]     = htmlspecialchars((string)($stored['organization'] ?? '-'));
        $fields[AdminText::get('delivery', 'Delivery')]             = htmlspecialchars((string)($stored['method'] ?? '-'))
            . (!empty($stored['shipping']) ? ' (' . htmlspecialchars($stored['shipping']) . ')' : '')
            . (isset($ds::TOKEN_MODELS[$model]) ? ' (' . htmlspecialchars($ds::TOKEN_MODELS[$model]) . ')' : '');
        $fields[AdminText::get('validity', 'Validity')] = (!empty($stored['validFrom']) ? htmlspecialchars(substr($stored['validFrom'], 0, 10)) : '-') . ' &rarr; '
            . (!empty($stored['validTill']) ? htmlspecialchars(substr($stored['validTill'], 0, 10)) : '-');
        if(!empty($stored['tokenSetupLink']))
        {
            $fields[AdminText::get('token_setup', 'Token set-up')] = '<a href="' . htmlspecialchars($stored['tokenSetupLink']) . '" target="_blank" rel="noopener">' . self::t('token_setup_page', 'Token set-up page') . '</a>';
        }

        return $fields + $this->getSmimePreviousOrders($stored);
    }

    /**
     * CodeGuard licence on the module tab: status, gateway / vendor licence, contact, period and the figures the
     * gateway reports (quotas, backups - empty while the gateway answers them as null)
     */
    private function getCodeGuardDetails($serviceId, $sslService)
    {
        $cg      = \ModulesGarden\TSSGGSModule\App\Libs\CodeGuardService::class;
        $stored  = $cg::stored($serviceId);
        $status  = (string)($stored['status'] ?? '');
        $labels  = ['active' => 'label-success', 'suspended' => 'label-warning', 'cancelled' => 'label-danger', 'refunded' => 'label-default'];
        $contact = (array)($stored['contact'] ?? []);
        $period  = $cg::period($serviceId);

        $fields = [];
        $fields[AdminText::get('configuration_status', 'Configuration Status')] = htmlspecialchars($sslService->status);
        $fields[AdminText::get('vendor_order', '%%VENDOR%% Order')] = self::orderLine($stored['orderId'] ?? 0, $stored['itemId'] ?? 0, $stored['vendorOrderId'] ?? '',
            'licence_number', 'Licence #:id:', 'vendor_licence', 'Vendor licence :id:');
        $fields[AdminText::get('vendor_status', 'Vendor Status')]   = self::vendorStatus($status, $labels, $stored['orderedAt'] ?? '', $stored['refreshedAt'] ?? '');
        $fields[AdminText::get('contact', 'Contact')] = $contact
            ? htmlspecialchars(trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''))) . ' &lt;' . htmlspecialchars($contact['email'] ?? '') . '&gt;'
            : '-';
        $fields[AdminText::get('period', 'Period')] = $period ? htmlspecialchars($period['start']) . ' &rarr; ' . htmlspecialchars($period['end'])
            . (empty($stored['subscriptionBegin']) ? ' <small class="text-muted">(' . self::t('period_months', ':months: months from the order date', ['months' => (int)($stored['term'] ?? 0)]) . ')</small>' : '') : '-';

        $license = $cg::licenseInfo($serviceId);
        if($license)
        {
            $rows = [];
            foreach($cg::licenseFigures($license) as $key => $value)
            {
                $rows[] = htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$key))) . ': ' . htmlspecialchars($value === true ? 'Yes' : (string)$value);
            }
            $fields[AdminText::get('licence', 'Licence')] = implode('<br>', $rows);
        }

        return $fields + $this->getSmimePreviousOrders($stored);
    }

    /**
     * Code signing order on the module tab: status, gateway / CA order, organization, delivery, validity
     */
    private function getCodeSigningDetails($serviceId, $sslService)
    {
        $cs     = \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::class;
        $stored = $cs::stored($serviceId);
        $status = (string)($stored['status'] ?? '');
        $labels = ['active' => 'label-success', 'processing' => 'label-info', 'pending' => 'label-info', 'cancelled' => 'label-danger',
                   'rejected' => 'label-danger', 'failed' => 'label-danger', 'revoked' => 'label-danger', 'expired' => 'label-default'];

        $delivery = $cs::describeDelivery([
            'method'   => $stored['method'] ?? '',
            'model'    => $stored['tokenModel'] ?? '',
            'shipping' => ['id' => $stored['shipping'] ?? ''],
        ]);

        $fields = [];
        $fields[AdminText::get('configuration_status', 'Configuration Status')] = htmlspecialchars($sslService->status);
        $fields[AdminText::get('vendor_order', '%%VENDOR%% Order')] = self::orderLine($stored['orderId'] ?? 0, $stored['itemId'] ?? 0, $stored['vendorOrderId'] ?? '');
        $fields[AdminText::get('vendor_status', 'Vendor Status')]   = self::vendorStatus($status, $labels, $stored['orderedAt'] ?? '', $stored['refreshedAt'] ?? '',
            !empty($stored['vendorStatus']) ? ' &nbsp;' . self::t('ca_status', 'CA: :status:', ['status' => $stored['vendorStatus']]) : '');
        $fields[AdminText::get('organization', 'Organization')] = htmlspecialchars((string)($stored['organization'] ?? '-'));
        $fields[AdminText::get('delivery', 'Delivery')]         = htmlspecialchars($delivery);
        if(!empty($stored['tokenInitCode']))
        {
            $fields[AdminText::get('token_init_code', 'Token Initialization Code')] = '<code>' . htmlspecialchars($stored['tokenInitCode']) . '</code> <small class="text-muted">' . self::t('token_init_code_hint', 'also shown to the client') . '</small>';
        }
        $fields[AdminText::get('validity', 'Validity')] = (!empty($stored['validFrom']) ? htmlspecialchars(substr($stored['validFrom'], 0, 10)) : '-') . ' &rarr; '
            . (!empty($stored['validTill']) ? htmlspecialchars(substr($stored['validTill'], 0, 10)) : '-');

        return $fields + $this->getSmimePreviousOrders($stored);
    }

    /**
     * S/MIME / code signing orders placed before the last renewal
     */
    private function getSmimePreviousOrders(array $stored)
    {
        if(empty($stored['previous']))
        {
            return [];
        }

        $previous = [];
        foreach(array_reverse((array)$stored['previous']) as $order)
        {
            $previous[] = self::t('order_number', 'Order #:id:', ['id' => (int)($order['orderId'] ?? 0)]) . ' (' . htmlspecialchars((string)($order['status'] ?? ''))
                . (!empty($order['validTill']) ? ', ' . self::t('valid_till', 'valid till :date:', ['date' => substr($order['validTill'], 0, 10)]) : '')
                . (!empty($order['emails']) ? ', ' . htmlspecialchars(implode(', ', (array)$order['emails'])) : '')
                . (!empty($order['organization']) ? ', ' . htmlspecialchars((string)$order['organization']) : '') . ')';
        }

        return [AdminText::get('previous_orders', 'Previous Orders') => implode('<br>', $previous)];
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
        $fields[AdminText::get('configuration_status', 'Configuration Status')] = htmlspecialchars($sslService->status);
        $fields[AdminText::get('vendor_order', '%%VENDOR%% Order')] = self::orderLine($order['order']['id'] ?? 0, $sslService->remoteid, $item['vendor']['id'] ?? '')
            . ' | ' . htmlspecialchars(Helpers::vendorToDisplay($params['configoption1']));
        $fields[AdminText::get('vendor_status', 'Vendor Status')] = self::vendorStatus($status, $labels, $order['order']['date'] ?? '', '')
            . ($synced ? ' &nbsp;| ' . self::t('last_synchronized', 'last synchronized :date:', ['date' => $synced]) : '');

        $validity = isset($stored['orderFiles']['validity']) ? $stored['orderFiles']['validity'] : [];
        if(!empty($validity['begin']) || !empty($item['subscription']['end']))
        {
            $fields[AdminText::get('validity', 'Validity')] = (!empty($validity['begin']) ? self::t('validity_certificate', 'Certificate :from: - :to:', ['from' => substr($validity['begin'], 0, 10), 'to' => substr((string)($validity['end'] ?? ''), 0, 10)]) : '')
                . (!empty($item['subscription']['end']) ? ' &nbsp;| ' . self::t('validity_subscription', 'Subscription :from: - :to:', ['from' => substr((string)$item['subscription']['begin'], 0, 10), 'to' => substr($item['subscription']['end'], 0, 10)]) : '');
        }

        $fields[AdminText::get('domain_validation', 'Domain Validation')] = $this->validationTable($params, $item, $status);

        // downloads as plain links (served by the AdminAreaPage hook) - WHMCS module buttons cannot deliver files
        $downloadUrl = 'clientsservices.php?userid=' . (int)$params['userid'] . '&id=' . $serviceId . '&token=' . urlencode(\generate_token('plain')) . '&tssggs_download=';
        $downloads   = [];
        if($status === 'active')
        {
            $downloads[] = '<a class="btn btn-default btn-sm" href="' . htmlspecialchars($downloadUrl . 'certificate') . '"><i class="fas fa-download"></i> ' . self::t('download_certificate_zip', 'Certificate (ZIP, all formats)') . '</a>';
        }
        foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
        {
            if(in_array($domain['method'] ?? '', ['http', 'https'], true) && !empty($domain['validation']['file_name']) && empty($domain['approved']))
            {
                $downloads[] = '<a class="btn btn-default btn-sm" href="' . htmlspecialchars($downloadUrl . 'authfile') . '"><i class="fas fa-file-download"></i> ' . self::t('download_validation_file', 'Validation file (:file:)', ['file' => basename($domain['validation']['file_name'])]) . '</a>';
                break;
            }
        }
        if($downloads)
        {
            $fields[AdminText::get('downloads', 'Downloads')] = implode(' ', $downloads);
        }

        if($status === 'active')
        {
            try
            {
                $bundle                = CertificateService::bundle($serviceId);
                $fields[AdminText::get('certificate', 'Certificate')] = '<details><summary style="cursor:pointer;">' . self::t('view_crt_bundle', 'View CRT / CA bundle') . '</summary>'
                    . '<textarea readonly rows="8" class="form-control" style="font-family:monospace;font-size:11px;margin-top:6px;">' . htmlspecialchars($bundle->crt()) . '</textarea>'
                    . ($bundle->ca() !== '' ? '<textarea readonly rows="8" class="form-control" style="font-family:monospace;font-size:11px;margin-top:6px;">' . htmlspecialchars($bundle->ca()) . '</textarea>' : '')
                    . '</details>';
            }
            catch(\Exception $e)
            {
            }

            $fields[AdminText::get('reissue', 'Reissue')] = '<textarea name="tssggs_reissue_csr" rows="4" class="form-control" style="font-family:monospace;font-size:11px;" placeholder="-----BEGIN CERTIFICATE REQUEST-----"></textarea>'
                . '<small class="text-muted">' . self::t('reissue_hint', 'Paste a new CSR and save to reissue the certificate with the current domains and validation methods.') . '</small>';
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

        $html = '<table class="table table-condensed" style="margin:0;max-width:760px;"><tr><th>' . self::t('domain', 'Domain') . '</th><th>' . self::t('method', 'Method') . '</th><th>' . self::t('status', 'Status') . '</th></tr>';
        $listed = [];
        foreach(isset($item['domains']) ? (array)$item['domains'] : [] as $domain)
        {
            $name     = (string)($domain['name'] ?? '');
            // an IP certificate sends its main name also as a SAN: the vendor lists it twice, shown once
            if(isset($listed[$name]))
            {
                continue;
            }
            $listed[$name] = true;
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
                    $methodCell .= '<option value="' . $option . '">' . self::t('change_to', 'change to :method:', ['method' => $option]) . '</option>';
                }
                $methodCell .= '</select> <input name="tssggs_approver[' . $key . ']" class="form-control input-sm" style="display:inline-block;width:200px;" placeholder="' . self::t('approver_email_placeholder', 'approver email (email method)') . '">';
            }

            $html .= '<tr><td>' . htmlspecialchars($name) . '</td><td>' . $methodCell . '</td><td>'
                . ($approved ? '<span class="label label-success">' . self::t('validated', 'Validated') . '</span>' : '<span class="label label-warning">' . self::t('pending', 'Pending') . '</span>') . '</td></tr>';
        }

        return $html . '</table>';
    }

    private function getAutoInstallDetails($serviceId, $userId)
    {
        try
        {
            $autoInstall = AutoInstallHelpers::getStatusForView($serviceId, 20);
        }
        catch(\Exception $ex)
        {
            return [];
        }

        // Suggestions: main domains of the client's cPanel accounts (no cPanel call on page load)
        $options = '';
        try
        {
            foreach(Cpanel::getServices($userId) as $cpanelService)
            {
                $options .= '<option value="' . htmlspecialchars($cpanelService->domain) . '">' . htmlspecialchars($cpanelService->name . ' - ' . $cpanelService->hostname) . '</option>';
            }
        }
        catch(\Throwable $e)
        {
        }

        $current = $autoInstall ? $autoInstall['domain'] : (string)CertificateService::installTarget($serviceId);
        $target  = '<input name="tssggs_install_domain" list="tssggs-install-targets" class="form-control input-sm" style="display:inline-block;width:260px;" value="' . htmlspecialchars($current) . '" placeholder="' . self::t('install_target_placeholder', 'domain on the client\'s cPanel account') . '">'
            . '<datalist id="tssggs-install-targets">' . $options . '</datalist>'
            . ' <small class="text-muted">' . ($autoInstall
                ? self::t('install_target_hint_ais', 'Domain the certificate is installed on (AutoInstall). Changing it re-queues the installation.')
                : ($options === ''
                    ? self::t('install_target_hint_no_cpanel', 'Default domain for "Install on server" in the client area - the client has no active cPanel account.')
                    : self::t('install_target_hint', 'Default domain for "Install on server" in the client area.'))) . '</small>';

        $installType   = AdminText::get('ssl_install_type', 'SSL Install Type');
        $installTarget = AdminText::get('install_target', 'Install Target');
        if(!$autoInstall)
        {
            // a certificate for public IPs is not installed on a cPanel account by domain
            if(\ModulesGarden\TSSGGSModule\App\Libs\IpCertificate::isProduct((int)\WHMCS\Database\Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('packageid')))
            {
                return [$installType => self::t('install_type_manual', 'Manual')];
            }

            return [$installType => self::t('install_type_manual', 'Manual'), $installTarget => $target];
        }

        $labels = [
            AutoInstallHelpers::STATUS_AWAITING  => 'label-info',
            AutoInstallHelpers::STATUS_INSTALLED => 'label-success',
            AutoInstallHelpers::STATUS_FAILED    => 'label-danger',
            AutoInstallHelpers::STATUS_DISABLED  => 'label-warning',
        ];
        $label = isset($labels[$autoInstall['status']]) ? $labels[$autoInstall['status']] : 'label-default';

        $status = '<span class="label ' . $label . '">' . htmlspecialchars(ucwords(str_replace('_', ' ', $autoInstall['status']))) . '</span>'
            . ' ' . self::t('installed_on', 'on') . ' <strong>' . htmlspecialchars($autoInstall['domain']) . '</strong>';
        if($autoInstall['installed_at'])
        {
            $status .= ' | ' . self::t('installed_at', 'installed: :date:', ['date' => $autoInstall['installed_at']]);
        }
        if($autoInstall['valid_till'])
        {
            $status .= ' | ' . self::t('valid_till_colon', 'valid till: :date:', ['date' => $autoInstall['valid_till']]);
        }
        if($autoInstall['last_error'])
        {
            $status .= '<br><span style="color:#c0392b;">' . self::t('last_error', 'Last error: :error:', ['error' => $autoInstall['last_error']]) . '</span>';
        }

        $return = [$installType => self::t('install_type_autoinstall', 'AutoInstall (cPanel)'), AdminText::get('autoinstall_status', 'AutoInstall Status') => $status, $installTarget => $target];

        if(!empty($autoInstall['logs']))
        {
            $logs = '<div style="max-height:220px;overflow:auto;font-size:11px;">';
            foreach($autoInstall['logs'] as $log)
            {
                $color = $log['type'] === 'error' ? '#c0392b' : ($log['type'] === 'success' ? '#27ae60' : '#333');
                $logs  .= '<div style="color:' . $color . ';">' . htmlspecialchars($log['date']) . ' - ' . htmlspecialchars($log['message']) . '</div>';
            }
            $logs                     .= '</div>';
            $return[AdminText::get('certificate_log', 'Certificate Log')] = $logs;
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

            if(isset($_POST['tssggs_renewal_days']))
            {
                CertificateService::setRenewalDaysOverride($serviceId, $_POST['tssggs_renewal_days']);
            }

            $installDomain = strtolower(trim((string)($_POST['tssggs_install_domain'] ?? '')));
            $state         = AutoInstallHelpers::get($serviceId);
            if(!$state && isset($_POST['tssggs_install_domain']) && $installDomain !== (string)CertificateService::installTarget($serviceId))
            {
                CertificateService::setInstallTarget($serviceId, $installDomain);
            }
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
        // a CSR without common name (IP certificates, App\Libs\IpCertificate): the current main name stays first
        if($cn === '')
        {
            $cn = (string)($order['common_name']['name'] ?? '');
        }

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
