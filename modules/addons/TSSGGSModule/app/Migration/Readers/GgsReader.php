<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Readers;

use ModulesGarden\TSSGGSModule\App\Migration\ProductMapper;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Configdata;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * GoGetSSL module "SSLCENTERWHMCS" (2.0.0 - 3.3.x). Data lives in tblsslorders.configdata plus the
 * mgfw_SSLCENTER_* / SSLCENTER_* tables. 1.x (GGSSLWHMCS) is not supported.
 */
class GgsReader extends AbstractReader
{
    const SETTINGS_TABLE = 'mgfw_SSLCENTER_api_configuration';

    const ACME_PRODUCT_IDS = [300];
    const AIS_PRODUCT_IDS  = [400, 401, 402, 403];

    const TABLES = [
        'mgfw_SSLCENTER_api_configuration', 'mgfw_SSLCENTER_api_product_prices', 'mgfw_SSLCENTER_user_commission',
        'mgfw_SSLCENTER_product_brand', 'mgfw_SSLCENTER_renew', 'mgfw_SSLCENTER_invoices_info', 'mgfw_SSLCENTER_invoices_pendingpayment',
        'SSLCENTER_logs', 'SSLCENTER_orders', 'SSLCENTER_actions', 'SSLCENTER_acme_subscriptions',
        'SSLCENTER_acme_subscription_domains', 'SSLCENTER_ais_subscriptions', 'SSLCENTER_cron_queue',
    ];

    /** @var ProductMapper|null */
    protected $mapper;
    protected $settingsRow = false;

    public function setMapper(ProductMapper $mapper): void
    {
        $this->mapper = $mapper;
    }

    public function source(): string      { return 'ggs'; }
    public function label(): string       { return 'GoGetSSL module (SSLCENTERWHMCS)'; }
    public function serverType(): string  { return 'SSLCENTERWHMCS'; }
    public function orderModules(): array { return ['SSLCENTERWHMCS']; }
    public function addonName(): string   { return 'SSLCENTERWHMCS'; }

    public function tables(): array
    {
        return array_values(array_filter(self::TABLES, [Schema::class, 'tableExists']));
    }

    public function versionSupport(): array
    {
        $version = $this->version();
        $legacy1 = Schema::tableExists('mgfw_ggssl_api_configuration') || Schema::count('tblproducts', ['servertype' => 'GGSSLWHMCS']) > 0;

        if($version === null)
        {
            if(Schema::tableExists(self::SETTINGS_TABLE))
            {
                return ['supported' => true, 'band' => 'full', 'reason' => 'Version not recorded; tables match 2.0.0 or newer.'];
            }
            if($legacy1)
            {
                return ['supported' => false, 'band' => 'unsupported', 'reason' => 'Only the 1.x module (GGSSLWHMCS) was found.'];
            }

            return ['supported' => false, 'band' => 'unsupported', 'reason' => 'Module version could not be determined.'];
        }

        if(version_compare($version, '2.0.0', '<'))
        {
            return ['supported' => false, 'band' => 'unsupported', 'reason' => 'Versions before 2.0.0 use a different database layout.'];
        }

        return ['supported' => true, 'band' => 'full', 'reason' => ''];
    }

    public function cronLines(): array
    {
        return array_map(function($file) { return 'php -q {WHMCS}/modules/addons/SSLCENTERWHMCS/cron/' . $file; }, [
            'cron.php', 'APIDailyStatusUpdater.php', 'APIProcessingOrders.php', 'certificateStatsLoader.php', 'notifier.php',
            'certificateSender.php', 'APIPriceUpdater.php', 'certificateDetailsUpdater.php', 'processCronQueue.php', 'checkActiveOrders.php',
        ]);
    }

    public function freeze(): array
    {
        return [];
    }

    // ---- Settings ---- //

    protected function settings(): array
    {
        if($this->settingsRow === false)
        {
            $row               = Schema::tableExists(self::SETTINGS_TABLE) ? Capsule::table(self::SETTINGS_TABLE)->first() : null;
            $this->settingsRow = $row ? (array)$row : [];
        }

        return $this->settingsRow;
    }

    public function readCredentials(): array
    {
        $s = $this->settings();
        // GGS 3.x sends "GGS <partner code>:<API password>" to the same gateway. Without a partner code the legacy
        // module only holds v1 API credentials (login + password), which the gateway does not accept.
        $code = trim((string)($s['api_partner_code'] ?? ''));

        return ['PartnerCode' => $code, 'AuthToken' => $code !== '' ? trim((string)($s['api_password'] ?? '')) : ''];
    }

    public function readSettings(): array
    {
        $s = $this->settings();
        if(!$s)
        {
            return ['sslSettings' => [], 'financeSettings' => [], 'legacy' => []];
        }

        $on  = function($key) use ($s) { return !empty($s[$key]) ? 'on' : ''; };
        $val = function($key, $default = '') use ($s) { return isset($s[$key]) && $s[$key] !== '' ? (string)$s[$key] : $default; };

        $ssl = [
            'recurringCreateAutomaticRenewalInvoice'             => $on('auto_renew_invoice_reccuring'),
            'recurringDaysBeforeExpiry'                          => $val('renew_invoice_days_reccuring', '30'),
            'recurringSendExpirationNotifications'               => $on('send_expiration_notification_reccuring') ? '30,15,7,3,1' : '',
            'oneTimeCreateAutomaticRenewalInvoice'               => $on('auto_renew_invoice_one_time'),
            'oneTimeDaysBeforeExpiry'                            => $val('renew_invoice_days_one_time', '30'),
            'oneTimeSendExpirationNotifications'                 => $on('send_expiration_notification_one_time') ? '30,15,7,3,1' : '',
            'recurringCreateAutomaticRenewalInvoiceSubscription' => $on('auto_renew_invoice_subscription'),
            'recurringDaysBeforeExpirySubscription'              => $val('renew_invoice_days_subscription', '30'),
            'recurringSendExpirationNotificationsSubscription'   => $on('send_expiration_notification_subscription') ? '30,15,7,3,1' : '',
            'renewOrderViaExistingOrder'                         => empty($s['renew_new_order']) ? 'on' : '',
            'automaticProcessingOfRenewalOrders'                 => $on('automatic_processing_of_renewal_orders'),
            'setTheUnpaidStatusForRenewalInvoices'               => $on('renewal_invoice_status_unpaid'),
            'visibleRenewButtonBeforeExpiration'                 => $on('visible_renew_button'),
            'visibleRenewButtonAfterExpiration'                  => $on('visible_renew_button'),
            'enableCsrGenerator'                                 => $on('display_csr_generator'),
            'useProfileDetailsForCsr'                            => $on('profile_data_csr'),
            'defaultCsrCountry'                                  => $val('default_csr_generator_country'),
            'disableEmailValidation'                             => $on('disable_email_validation'),
            'autoDetailsForDvOrders'                             => $on('use_admin_contact'),
            'wildcardDnsTimeoutMinutes'                          => $val('wildcard_dns_timeout_minutes', '65'),
            'autoReissueDaysBeforeExpire'                        => $val('auto_reissue_days_before_expire', '7'),
        ];

        $tech = ['firstName' => 'tech_firstname', 'lastName' => 'tech_lastname', 'companyName' => 'tech_organization', 'jobTitle' => 'tech_title',
                 'emailAddress' => 'tech_email', 'address' => 'tech_addressline1', 'city' => 'tech_city', 'stateRegion' => 'tech_region',
                 'country' => 'tech_country', 'postCode' => 'tech_postalcode', 'zipCode' => 'tech_postalcode'];
        $hasTech = false;
        foreach($tech as $unified => $legacy)
        {
            $ssl[$unified] = $val($legacy);
            $hasTech       = $hasTech || $ssl[$unified] !== '';
        }
        $ssl['useFixedTechnicalDetails'] = $hasTech ? 'on' : '';

        $template = (int)$val('send_certificate_template') ? (string)Capsule::table('tblemailtemplates')->where('id', (int)$val('send_certificate_template'))->value('name') : '';

        return [
            'sslSettings'     => $ssl,
            'financeSettings' => ['rate' => (float)$val('rate', '1') ?: 1],
            // General settings page (client area, messages, logging)
            'generalSettings' => array_filter([
                'clientSummary'            => !empty($s['display_ca_summary']),
                'sidebarTemplates'         => $val('sidebar_templates'),
                'expiresSoonDays'          => (int)$val('summary_expires_soon_days', '30'),
                'processingGuide'          => $val('custom_guide'),
                'certificateEmailTemplate' => $template,
                'whmcsActivityLog'         => !empty($s['save_activity_logs']),
            ], function($value) { return $value !== '' && $value !== null; }),
            // informational: things the unified module has no setting for
            'legacy'          => [
                'api_partner_code'     => $val('api_partner_code'),
                'custom_guide'         => $val('custom_guide'),
                'display_ca_summary'   => $on('display_ca_summary'),
                'auto_install_cpanel'  => $on('auto_install_cpanel'),
                'save_activity_logs'   => $on('save_activity_logs'),
            ],
        ];
    }

    // ---- Products ---- //

    public function readProduct(int $pid): array
    {
        $p = (array)Capsule::table('tblproducts')->where('id', $pid)->first();

        return [
            'pid'                   => $pid,
            'name'                  => (string)$p['name'],
            'legacy_product_ref'    => (string)$p['configoption1'],
            'remote_product'        => $this->mapper ? $this->mapper->forGgs($p['configoption1']) : null,
            'term_months'           => (int)$p['configoption2'] ?: 12,
            'included_san'          => (int)$p['configoption4'],
            'included_san_wildcard' => (int)$p['configoption8'],
            'price_auto'            => (string)$p['configoption5'] === '1',
            'san_options'           => $this->sanOptions($pid, ['sans' => '/^sans_count(\||$)/', 'sans_wildcard' => '/^sans_wildcard_count(\||$)/']),
            'other_options'         => [],
            // per-product messages and the product commission (a fraction, 0.1 = +10 % for every client)
            'product_settings'      => array_filter([
                'issuedMessage'   => trim((string)$p['configoption23']),
                'processingGuide' => trim((string)$p['configoption24']),
            ], 'strlen'),
            'commission_percent'    => (float)$p['configoption6'] > 0 ? round((float)$p['configoption6'] * 100, 2) : null,
            'warnings'              => [],
        ];
    }

    /**
     * Per-client commissions: [['client_id', 'product_id', 'percent']] (stored by the GGS module as a fraction)
     */
    public function readCommissions(): array
    {
        if(!Schema::tableExists('mgfw_SSLCENTER_user_commission'))
        {
            return [];
        }

        $rules = [];
        foreach(Capsule::table('mgfw_SSLCENTER_user_commission')->get() as $row)
        {
            if(is_numeric($row->commission))
            {
                $rules[] = ['client_id' => (int)$row->client_id, 'product_id' => (int)$row->product_id, 'percent' => round((float)$row->commission * 100, 2)];
            }
        }

        return $rules;
    }

    // ---- Services ---- //

    public function readService(int $serviceId): array
    {
        $svc = $this->service($serviceId);
        if(!$svc)
        {
            return ['service_id' => $serviceId, 'kind' => 'ssl', 'skip_reason' => 'Service not found'];
        }

        $order = $this->sslOrder($serviceId);
        $rec   = [
            'service_id'   => $serviceId,
            'client_id'    => (int)$svc['userid'],
            'product_id'   => (int)$svc['packageid'],
            'ssl_order_id' => $order ? (int)$order['id'] : 0,
            'kind'         => 'ssl',
            'warnings'     => [],
            'skip_reason'  => null,
            'preordered'   => false,
        ];

        if(!$order)
        {
            $rec['skip_reason'] = $svc['domainstatus'] === 'Pending' ? 'Unpaid: the SSL order is created by the unified module when the invoice is paid' : 'No SSL order row for this service';

            return $rec;
        }

        $cd           = Configdata::decode($order['configdata']);
        $apiProductId = (int)$svc['configoption1'];
        $rec['legacy_status'] = (string)$order['status'];

        if(in_array($apiProductId, self::ACME_PRODUCT_IDS, true) || !empty($cd['subscription']))
        {
            return $this->readAcme($rec, $order, $cd);
        }
        if(in_array($apiProductId, self::AIS_PRODUCT_IDS, true) || Schema::count('SSLCENTER_ais_subscriptions', ['service_id' => $serviceId]) > 0)
        {
            return $this->readAis($rec, $order, $cd);
        }
        $remote = $this->mapper ? $this->mapper->forGgs(Configdata::get($cd, 'product_id', $svc['configoption1'])) : null;
        if(!empty($cd['smime_item_id']) || ($remote && strtolower((string)$remote->category) === 'smime'))
        {
            return $this->readSmime($rec, $svc, $order, $cd, $remote);
        }

        return $this->readSsl($rec, $svc, $order, $cd);
    }

    protected function readSsl(array $rec, array $svc, array $order, array $cd): array
    {
        $remoteProduct = $this->mapper ? $this->mapper->forGgs(Configdata::get($cd, 'product_id', $svc['configoption1'])) : null;
        $remoteId      = (string)$order['remoteid'];
        $itemId        = (int)Configdata::get($cd, 'tls_item_id', 0);

        $rec['remote_id']         = $itemId > 0 ? (string)$itemId : $remoteId;
        $rec['remote_product_id'] = $remoteProduct ? (string)$remoteProduct->remoteId : (string)(int)Configdata::get($cd, 'product_id', $svc['configoption1']);
        if(!$remoteProduct)
        {
            $rec['warnings'][] = 'Gateway product not found for GoGetSSL product id ' . $rec['remote_product_id'] . ' (sync products in step 4)';
        }

        if($order['status'] === self::STATUS_AWAITING || $remoteId === '')
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }

        $gatewayStatus         = $this->normalizeStatus((string)Configdata::get($cd, 'ssl_status', ''), (string)$order['status']);
        $rec['request_status'] = $gatewayStatus;
        $rec['target_status']  = $this->targetSslStatus((string)$order['status'], $gatewayStatus);

        $commonName = strtolower((string)Configdata::get($cd, 'domain', $svc['domain']));
        $csr        = Configdata::pem(Configdata::get($cd, 'csr', ''));
        $dcvMethod  = $this->cnMethod((string)Configdata::get($cd, 'dcv_method', ''));
        $approver   = (string)Configdata::get($cd, 'approveremail', '');
        if($dcvMethod === 'email' && strpos($approver, '@') !== false)
        {
            $dcvMethod = $approver;
        }

        // SANs: san_details (after order) or the configure-step list
        $sans = [];
        foreach((array)Configdata::get($cd, 'san_details', []) as $san)
        {
            $name = strtolower((string)($san['san_name'] ?? $san['name'] ?? ''));
            if($name === '' || $name === $commonName)
            {
                continue;
            }
            $method = $this->cnMethod((string)($san['validation_method'] ?? $dcvMethod));
            $sans[] = ['name' => $name, 'validation_method' => $method, 'approved' => in_array(strtolower((string)($san['status'] ?? '')), ['active', 'validated', 'approved', 'valid'], true)];
        }
        if(!$sans && isset($cd['fields']['sans_domains']))
        {
            foreach(Configdata::domainList($cd['fields']['sans_domains']) as $name)
            {
                if($name !== $commonName)
                {
                    $sans[] = ['name' => $name, 'validation_method' => $dcvMethod];
                }
            }
        }

        $admin = [
            'first_name' => Configdata::get($cd, 'admin_firstname', '', ['firstname']), 'last_name' => Configdata::get($cd, 'admin_lastname', '', ['lastname']),
            'title'      => Configdata::get($cd, 'admin_title', '', ['jobtitle']), 'email' => Configdata::get($cd, 'admin_email', '', ['email']),
            'phone'      => Configdata::get($cd, 'admin_phone', '', ['phonenumber']),
        ];
        $tech = [
            'first_name' => Configdata::get($cd, 'tech_firstname', $admin['first_name']), 'last_name' => Configdata::get($cd, 'tech_lastname', $admin['last_name']),
            'title'      => Configdata::get($cd, 'tech_title', $admin['title']), 'email' => Configdata::get($cd, 'tech_email', $admin['email']),
            'phone'      => Configdata::get($cd, 'tech_phone', $admin['phone']),
        ];
        $f   = (array)($cd['fields'] ?? []);
        $org = [
            'name'       => Configdata::get($f, 'org_name', Configdata::get($cd, 'orgname', Configdata::get($cd, 'admin_organization', ''))),
            'division'   => Configdata::get($f, 'org_division', ''), 'duns' => Configdata::get($f, 'org_duns', ''),
            'address'    => Configdata::get($f, 'org_addressline1', Configdata::get($cd, 'address1', '')), 'city' => Configdata::get($f, 'org_city', Configdata::get($cd, 'city', '')),
            'region'     => Configdata::get($f, 'org_regions', Configdata::get($cd, 'state', '')), 'country' => Configdata::get($f, 'org_country', Configdata::get($cd, 'country', '')),
            'postalcode' => Configdata::get($f, 'org_postalcode', Configdata::get($cd, 'postcode', '')), 'phone' => Configdata::get($f, 'org_phone', ''),
        ];

        $payload = $this->buildOrderPayload($rec['remote_product_id'], (int)$svc['configoption2'], $csr, $commonName, $dcvMethod,
            array_map(function($s) { return ['name' => $s['name'], 'validation_method' => $s['validation_method']]; }, $sans),
            $this->buildContacts($admin, $tech, $org));
        $rec['order_payload'] = $payload;
        if($csr === '')
        {
            $rec['warnings'][] = 'No CSR stored; reissue will require a new CSR';
        }

        $crt = Configdata::pem(Configdata::get($cd, 'crt', ''));
        $ca  = Configdata::pem(Configdata::get($cd, 'ca', ''));
        $domains = array_merge([['name' => $commonName, 'method' => $dcvMethod, 'approved' => $gatewayStatus === 'active']], array_map(function($s) use ($gatewayStatus) {
            return ['name' => $s['name'], 'method' => $s['validation_method'], 'approved' => $s['approved'] ?? ($gatewayStatus === 'active')];
        }, $sans));

        $rec['certificate'] = $this->buildCertificate([
            'status'             => $gatewayStatus,
            'item_id'            => $itemId > 0 ? $itemId : (int)$remoteId,
            'order_id'           => (int)$remoteId,
            'vendor_order_id'    => (string)Configdata::get($cd, 'partner_order_id', ''),
            'remote_product_id'  => $rec['remote_product_id'],
            'csr'                => $csr,
            'common_name'        => $commonName,
            'domains'            => $domains,
            'crt'                => $crt,
            'ca'                 => $ca,
            'valid_from'         => (string)Configdata::get($cd, 'valid_from', ''),
            'valid_till'         => (string)Configdata::get($cd, 'valid_till', ''),
            'subscription_begin' => (string)Configdata::get($cd, 'begin_date', Configdata::get($cd, 'valid_from', '')),
            'subscription_end'   => (string)Configdata::get($cd, 'end_date', Configdata::get($cd, 'valid_till', '')),
            'order_date'         => (string)$order['completiondate'],
            'order_payload'      => $payload,
            'api_price'          => $this->apiPrice($rec['remote_product_id'], (int)$svc['configoption2']),
        ]);
        if($gatewayStatus === 'active' && $crt === '')
        {
            $rec['warnings'][] = 'Certificate files missing in legacy data; they are fetched by the first sync';
        }

        $rec['prices'] = [
            'api_price'   => $this->apiPrice($rec['remote_product_id'], (int)$svc['configoption2']),
            'whmcs_price' => (float)$svc['amount'],
            'invoiceid'   => $this->lastInvoiceId($rec['service_id']),
        ];

        // AutoInstall (3.1+: install_type; older: global flag + SSLCENTER_orders)
        $installType = (string)Configdata::get($cd, 'install_type', '');
        $legacyOrder = Schema::tableExists('SSLCENTER_orders') ? Capsule::table('SSLCENTER_orders')->where('service_id', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        $settings    = $this->settings();
        $isAuto      = $installType === 'autoinstall' || ($installType === '' && !empty($settings['auto_install_cpanel']) && $legacyOrder && in_array($legacyOrder->status, ['Pending Verification', 'Pending Installation', 'Success'], true));

        $rec['private_key'] = Configdata::privateKeyPem(Configdata::get($cd, 'private_key', ''));
        $rec['autoinstall'] = null;
        $rec['queue']       = [];

        if($isAuto && in_array($gatewayStatus, ['active', 'pending', 'processing'], true))
        {
            $installDomain = preg_replace('/^\*\./', '', $commonName);
            $cpanel        = $this->cpanelService($rec['client_id'], $installDomain, (int)Configdata::get($cd, 'cpanel_service_id', 0));
            if(!$cpanel)
            {
                $rec['warnings'][] = 'AutoInstall: no Active cPanel account of this client serves ' . $installDomain . '; migrated as manual install';
            }
            elseif($rec['private_key'] === '' )
            {
                $rec['warnings'][] = 'AutoInstall: private key missing or not decryptable; migrated as manual install (re-key at next reissue)';
            }
            else
            {
                $installed = ($legacyOrder && $legacyOrder->status === 'Success') || $this->hasQueueDone($rec['service_id'], 'install');
                $rec['autoinstall'] = [
                    'install_type'       => 'autoinstall',
                    'domain'             => $installDomain,
                    'common_name'        => $commonName,
                    'dcv_method'         => $this->cnMethod($dcvMethod),
                    'dns_fallback_email' => '',
                    'is_wildcard'        => (bool)Configdata::get($cd, 'is_wildcard', strpos($commonName, '*.') === 0),
                    'created_at'         => (string)$order['completiondate'] ?: date('Y-m-d H:i:s'),
                    'installed_crt_hash' => $installed && $crt !== '' ? sha1(trim($crt)) : '',
                    'installed_at'       => $installed ? (string)($legacyOrder->date ?? date('Y-m-d H:i:s')) : '',
                    'api_order_id'       => (int)$rec['remote_id'],
                    'valid_till'         => (string)Configdata::get($cd, 'valid_till', ''),
                    'subscription_end'   => (string)Configdata::get($cd, 'end_date', Configdata::get($cd, 'valid_till', '')),
                    'last_error'         => '',
                    'cpanel_service_id'  => (int)$cpanel['id'],
                    'status'             => $installed && $gatewayStatus === 'active' ? 'installed' : 'awaiting_validation',
                    'legacy_status'      => $legacyOrder ? (string)$legacyOrder->status : '',
                ];

                foreach($this->openQueue($rec['service_id']) as $task)
                {
                    $rec['queue'][] = $task;
                }
                if(!$rec['queue'] && $rec['autoinstall']['status'] !== 'installed')
                {
                    $rec['queue'][] = ['type' => 'validate', 'data' => ['domain' => $installDomain, 'api_order_id' => (int)$rec['remote_id'], 'is_wildcard' => $rec['autoinstall']['is_wildcard']]];
                }
            }
        }

        return $rec;
    }

    protected function readAcme(array $rec, array $order, array $cd): array
    {
        $rec['kind'] = 'acme';
        $sub = Schema::tableExists('SSLCENTER_acme_subscriptions') ? Capsule::table('SSLCENTER_acme_subscriptions')->where('service_id', $rec['service_id'])->first() : null;
        if(!$sub)
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }
        $sub = (array)$sub;
        $domains = Schema::tableExists('SSLCENTER_acme_subscription_domains')
            ? array_map(function($r) { return (array)$r; }, Capsule::table('SSLCENTER_acme_subscription_domains')->where('service_id', $rec['service_id'])->get()->all()) : [];

        $rec['remote_id']      = (string)$sub['api_order_id'];
        $rec['target_status']  = (string)$order['status'] === self::STATUS_AWAITING ? self::STATUS_AWAITING : 'Completed';
        $rec['request_status'] = (string)$sub['status'] ?: 'pending';
        $rec['subscription']   = ['acme' => [
            'client_id' => $rec['client_id'], 'api_order_id' => (int)$sub['api_order_id'], 'acme_id' => (int)($sub['acme_id'] ?? 0),
            'status' => (string)$sub['status'], 'acme_account_id' => (string)$sub['acme_account_id'], 'eab_kid' => (string)$sub['eab_kid'],
            'eab_hmac_key' => (string)$sub['eab_hmac_key'], 'server_url' => (string)$sub['server_url'],
            'period_start' => $sub['period_start'], 'period_end' => $sub['period_end'], 'renewal_date' => $sub['renewal_date'],
            'auto_renew' => (int)($sub['auto_renew'] ?? 1), 'cancelled_at' => $sub['cancelled_at'],
            'domains' => array_map(function($d) { return ['domain' => strtolower($d['domain']), 'domain_type' => $d['domain_type'] ?: 'single', 'status' => $d['status'] ?: 'added', 'added_at' => $d['added_at'] ?? null, 'removed_at' => $d['removed_at'] ?? null]; }, $domains),
        ]];
        $rec['configdata'] = ['subscription' => true, 'ssl_status' => (string)$sub['status'], 'begin_date' => $sub['period_start'], 'end_date' => $sub['period_end'], 'renewal_date' => $sub['renewal_date']];

        return $rec;
    }

    protected function readAis(array $rec, array $order, array $cd): array
    {
        $rec['kind'] = 'ais';
        $sub = Schema::tableExists('SSLCENTER_ais_subscriptions') ? Capsule::table('SSLCENTER_ais_subscriptions')->where('service_id', $rec['service_id'])->first() : null;
        if(!$sub)
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }
        $sub = (array)$sub;
        $rec['remote_id']      = (string)$sub['api_order_id'];
        $rec['target_status']  = strtolower((string)$sub['status']) === 'active' ? 'Completed' : self::STATUS_SUBMITTED;
        $rec['request_status'] = (string)$sub['status'] ?: 'pending';
        $rec['subscription']   = ['ais' => [
            'client_id' => $rec['client_id'], 'api_order_id' => (int)$sub['api_order_id'], 'ais_item_id' => (int)$sub['ais_item_id'],
            'status' => (string)$sub['status'], 'login_sso_link' => (string)$sub['login_sso_link'], 'manage_sso_link' => (string)$sub['manage_sso_link'],
            'autoinstall_status' => (string)$sub['autoinstall_status'], 'period_start' => $sub['period_start'], 'period_end' => $sub['period_end'],
            'renewal_date' => $sub['renewal_date'], 'auto_renew' => (int)($sub['auto_renew'] ?? 1), 'cancelled_at' => $sub['cancelled_at'],
        ]];
        $rec['configdata'] = ['ssl_status' => (string)$sub['status']];

        return $rec;
    }

    /**
     * S/MIME (SSLCENTERWHMCS 3.4+, ordered through GoGetSSL API v2): configdata smime_item_id, smime_order_id, ssl_status,
     * smime_emails, smime_term, smime_csr_provided, valid_from, valid_till, ordered_at, partner_order_id.
     * Written as the unified module's "smime" request row (SmimeService).
     */
    protected function readSmime(array $rec, array $svc, array $order, array $cd, $remote): array
    {
        $rec['kind']              = 'smime';
        $rec['remote_product_id'] = $remote ? (string)$remote->remoteId : (string)(int)Configdata::get($cd, 'product_id', $svc['configoption1']);
        if(!$remote)
        {
            $rec['warnings'][] = 'Gateway product not found for GoGetSSL product id ' . $rec['remote_product_id'] . ' (sync products in step 4)';
        }

        $itemId = (int)Configdata::get($cd, 'smime_item_id', 0);
        if($itemId <= 0 || $order['status'] === self::STATUS_AWAITING)
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }

        $status = strtolower((string)Configdata::get($cd, 'ssl_status', '')) ?: 'processing';
        $emails = [];
        foreach((array)Configdata::get($cd, 'smime_emails', []) as $entry)
        {
            $entry = is_string($entry) ? ['email' => $entry] : (array)$entry;
            if(!empty($entry['email']))
            {
                $emails[] = ['email' => (string)$entry['email'], 'approved' => !empty($entry['approved'])];
            }
        }

        $rec['remote_id']      = (string)$itemId;
        $rec['request_status'] = $status;
        $rec['target_status']  = $status === 'active' ? 'Completed' : (in_array($status, ['cancelled', 'revoked', 'refunded'], true) ? 'Cancelled' : self::STATUS_SUBMITTED);
        $rec['smime'] = [
            'orderId'       => (int)Configdata::get($cd, 'smime_order_id', $order['remoteid']),
            'itemId'        => $itemId,
            'status'        => $status,
            'emails'        => $emails,
            'vendorOrderId' => (string)Configdata::get($cd, 'partner_order_id', ''),
            'validFrom'     => (string)Configdata::get($cd, 'valid_from', ''),
            'validTill'     => (string)Configdata::get($cd, 'valid_till', ''),
            'term'          => (int)Configdata::get($cd, 'smime_term', 12),
            'csrProvided'   => (bool)Configdata::get($cd, 'smime_csr_provided', false),
            'orderedAt'     => (string)Configdata::get($cd, 'ordered_at', ''),
            'previous'      => [],
            // the legacy module may have reminded the client already: no reminder e-mails for migrated orders
            'remindersSent' => [2, 7],
            'migrated'      => true,
        ];
        $rec['prices'] = [
            'api_price'   => $this->apiPrice((int)$rec['remote_product_id'], (int)Configdata::get($cd, 'smime_term', 12)),
            'whmcs_price' => (float)$svc['amount'],
            'invoiceid'   => $this->lastInvoiceId($rec['service_id']),
        ];
        if(!$emails)
        {
            $rec['warnings'][] = 'S/MIME email addresses missing in legacy data; they are read from the gateway by the first refresh';
        }

        return $rec;
    }

    // ---- helpers ---- //

    protected function apiPrice(int $apiProductId, int $months): float
    {
        if(!Schema::tableExists('mgfw_SSLCENTER_api_product_prices'))
        {
            return 0.0;
        }
        $row = Capsule::table('mgfw_SSLCENTER_api_product_prices')->where('api_product_id', $apiProductId)->where('period', $months ?: 12)->first();

        return $row ? (float)$row->price : 0.0;
    }

    protected function lastInvoiceId(int $serviceId): int
    {
        return (int)Capsule::table('tblinvoiceitems')->where('type', 'Hosting')->where('relid', $serviceId)->orderBy('id', 'desc')->value('invoiceid');
    }

    protected function openQueue(int $serviceId): array
    {
        if(!Schema::tableExists('SSLCENTER_cron_queue'))
        {
            return [];
        }
        $tasks = [];
        foreach(Capsule::table('SSLCENTER_cron_queue')->where('service_id', $serviceId)->whereIn('status', ['pending', 'processing'])->orderBy('id')->get() as $row)
        {
            $tasks[] = ['type' => (string)$row->type, 'data' => json_decode((string)$row->data, true) ?: []];
        }

        return $tasks;
    }

    protected function hasQueueDone(int $serviceId, string $type): bool
    {
        return Schema::tableExists('SSLCENTER_cron_queue')
            && Capsule::table('SSLCENTER_cron_queue')->where('service_id', $serviceId)->where('type', $type)->where('status', 'done')->exists();
    }

    // ---- Detection counts ---- //

    public function counts(): array
    {
        $serverType = $this->serverType();
        $products   = Capsule::table('tblproducts')->selectRaw('COUNT(*) AS c, SUM(hidden) AS h, SUM(retired) AS r')->where('servertype', $serverType)->first();

        return [
            'products'                => ['total' => (int)$products->c, 'hidden' => (int)$products->h, 'retired' => (int)$products->r],
            'services'                => $this->countBy('tblhosting', 'domainstatus', function($q) use ($serverType) { $q->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')->where('tblproducts.servertype', $serverType); }),
            'orders'                  => $this->countBy('tblsslorders', 'status', function($q) { $q->whereIn('module', $this->orderModules()); }),
            'acme'                    => Schema::count('SSLCENTER_acme_subscriptions'),
            'ais'                     => Schema::count('SSLCENTER_ais_subscriptions'),
            'autoinstall'             => Schema::tableExists('tblsslorders') ? (int)Capsule::table('tblsslorders')->whereIn('module', $this->orderModules())->where('configdata', 'LIKE', '%"install_type":"autoinstall"%')->count() : 0,
            'queue_open'              => Schema::tableExists('SSLCENTER_cron_queue') ? Schema::count('SSLCENTER_cron_queue', ['status' => ['pending', 'processing']]) : 0,
            'unconfigured'            => Schema::count('tblsslorders', ['module' => $this->orderModules(), 'status' => self::STATUS_AWAITING]),
            'unconfigured_preordered' => 0,
            'unpaid'                  => (int)Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')->where('tblproducts.servertype', $serverType)->where('tblhosting.domainstatus', 'Pending')->count(),
            'commissions'             => Schema::count('mgfw_SSLCENTER_user_commission'),
            'unsupported'             => [],
            'other_ssl_modules'       => $this->otherSslModules(),
        ];
    }
}
