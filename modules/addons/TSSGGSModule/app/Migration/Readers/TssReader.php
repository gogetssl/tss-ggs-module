<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Readers;

use ModulesGarden\TSSGGSModule\App\Migration\ProductMapper;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Configdata;
use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * TheSSLStore module "thesslstorefullv2" (3.3 - 5.8.x). Orders live in tblsslorders.configdata +
 * mod_sslstore_orders_data; subscriptions in tss_subscription; AutoInstall in mod_autoinstallssl_orders.
 * Certificates and private keys are never stored by this module (fetched from TSS / kept in cPanel).
 */
class TssReader extends AbstractReader
{
    const SUBSCRIPTION_CODES = ['rapidssl_automate', 'rapidssl_wc_automate', 'geotrust_automate', 'geotrust_wc_automate'];
    const ACME_CODES         = ['sectigo_caas_dv', 'positivessl_caas_dv'];
    /** S/MIME products (migrated as S/MIME orders, see readSmime()) */
    const SMIME_CODES = ['pacbasic', 'pacpro', 'pacenterprise', 'digi_class1_smime', 'digi_client_premium'];
    /** product code fragments the unified module has no flow for */
    const UNSUPPORTED_FRAGMENTS = ['cwatch', 'codeguard', 'sitelock', 'codesign', '_csc', '_cs_', 'smime', 'pac', 'vmc', 'cmc', 'scan', 'pci', 'ee_'];

    const TABLES = [
        'mod_sslstore_configuration', 'mod_sslstore_settings_v2', 'mod_sslstore_product_data', 'mod_sslstore_sync_products', 'mod_sslstore_retired_options',
        'mod_sslstore_orders_data', 'mod_sslstore_certificates', 'mod_sslstore_org_data', 'mod_sslstore_invoice_log', 'mod_sslstore_importorders_v2',
        'mod_sslstore_upgrade_order', 'mod_sslstore_renewal_order_check', 'mod_sslstore_panel_orders', 'mod_sslstore_auto_install_ssl',
        'mod_sslstore_crons', 'mod_sslstore_cron_status', 'mod_sslstore_hash_files', 'mod_sslstore_logs',
        'mod_autoinstallssl_orders', 'mod_autoinstallssl_domains', 'mod_autoinstallssl_invoices', 'mod_autoinstallssl_logs',
        'tss_subscription', 'tss_acme_eab', 'tss_subscription_renewal_log',
    ];

    /** @var ProductMapper|null */
    protected $mapper;
    protected $config = false;

    public function setMapper(ProductMapper $mapper): void
    {
        $this->mapper = $mapper;
    }

    public function source(): string      { return 'tss'; }
    public function label(): string       { return 'TheSSLStore module (thesslstorefullv2)'; }
    public function serverType(): string  { return 'thesslstorefullv2'; }
    public function orderModules(): array { return ['thesslstorefullv2']; }
    public function addonName(): string   { return 'thesslstorefullv2_admin'; }

    public function tables(): array
    {
        return array_values(array_filter(self::TABLES, [Schema::class, 'tableExists']));
    }

    public function versionSupport(): array
    {
        $version = $this->version();
        if($version === null)
        {
            if(Schema::tableExists('mod_sslstore_configuration') && Schema::tableExists('mod_sslstore_orders_data'))
            {
                return ['supported' => true, 'band' => 'full', 'reason' => 'Version not recorded; tables match 3.8 or newer.'];
            }

            return ['supported' => false, 'band' => 'unsupported', 'reason' => 'Module version could not be determined.'];
        }
        if(version_compare($version, '3.3', '<'))
        {
            return ['supported' => false, 'band' => 'unsupported', 'reason' => 'Versions before 3.3 store product codes and orders differently.'];
        }

        return ['supported' => true, 'band' => 'full', 'reason' => ''];
    }

    public function cronLines(): array
    {
        return ['php -q {WHMCS}/modules/addons/thesslstorefullv2_admin/tss_cron.php'];
    }

    public function freeze(): array
    {
        $done = [];
        if(Schema::tableExists('mod_sslstore_settings_v2') && Schema::columnExists('mod_sslstore_settings_v2', 'product_sync_setting'))
        {
            Capsule::table('mod_sslstore_settings_v2')->update(['product_sync_setting' => 'manually']);
            $done[] = 'product_sync_setting=manually';
        }
        if(Schema::tableExists('mod_sslstore_crons'))
        {
            // a very long interval keeps the rows (for rollback) but stops the legacy scheduler from acting
            Capsule::table('mod_sslstore_crons')->update(['interval' => 525600]);
            $done[] = 'cron intervals set to 1 year';
        }

        return $done;
    }

    // ---- Settings ---- //

    protected function config(): array
    {
        if($this->config === false)
        {
            $this->config = [];
            if(Schema::tableExists('mod_sslstore_configuration'))
            {
                foreach(Capsule::table('mod_sslstore_configuration')->get() as $row)
                {
                    $this->config[(string)$row->config_key] = (string)$row->config_value;
                }
            }
        }

        return $this->config;
    }

    public function readCredentials(): array
    {
        $c      = $this->config();
        $secret = function($key) use ($c) { $v = trim((string)($c[$key] ?? '')); return $v !== '' && function_exists('decrypt') ? trim((string)decrypt($v)) : $v; };

        $out = [
            'PartnerCode'     => trim((string)($c['live_partner_code'] ?? '')),
            'AuthToken'       => $secret('live_auth_token'),
            'TestPartnerCode' => trim((string)($c['test_partner_code'] ?? '')),
            'TestAuthToken'   => $secret('test_auth_token'),
            'OperationMode'   => strtoupper((string)($c['api_mode'] ?? '')) === 'LIVE' ? 'live' : 'sandbox',
        ];
        if($out['AuthToken'] !== '' || $out['TestAuthToken'] !== '')
        {
            return $out;
        }

        // 3.3 - 3.7: the credentials are plain columns of mod_sslstore_settings_v2
        $old = $this->oldCredentials();

        return $old ?: $out;
    }

    /** Credentials as versions 3.3 - 3.7 store them (null when that layout is not there or holds nothing) */
    protected function oldCredentials(): ?array
    {
        if(!Schema::tableExists('mod_sslstore_settings_v2') || !Schema::columnExists('mod_sslstore_settings_v2', 'live_authtoken'))
        {
            return null;
        }
        $v2  = (array)Capsule::table('mod_sslstore_settings_v2')->first();
        $get = function($key) use ($v2) { return trim((string)($v2[$key] ?? '')); };
        if($get('live_authtoken') === '' && $get('test_authtoken') === '')
        {
            return null;
        }

        return [
            'PartnerCode'     => $get('live_partnercode'),
            'AuthToken'       => $get('live_authtoken'),
            'TestPartnerCode' => $get('test_partnercode'),
            'TestAuthToken'   => $get('test_authtoken'),
            'OperationMode'   => strtoupper($get('operationmode')) === 'LIVE' ? 'live' : 'sandbox',
        ];
    }

    public function readSettings(): array
    {
        $c = $this->config();
        if(!$c && !Schema::tableExists('mod_sslstore_settings_v2'))
        {
            return ['sslSettings' => [], 'financeSettings' => [], 'legacy' => []];
        }
        $val = function($key, $default = '') use ($c) { return isset($c[$key]) && $c[$key] !== '' ? $c[$key] : $default; };
        $on  = function($key) use ($c) { return !empty($c[$key]) && !in_array(strtolower($c[$key]), ['0', 'no', 'off', 'false'], true) ? 'on' : ''; };

        $reminderDays = (int)$val('expiration_reminder_email_days', '7');
        $notify       = implode(',', array_unique(array_filter([30, 15, $reminderDays, 7, 3, 1], function($d) use ($reminderDays) { return $d <= max(30, $reminderDays); })));
        $autorenew    = strtolower($val('autorenew', ''));

        $ssl = [
            'recurringCreateAutomaticRenewalInvoice' => in_array($autorenew, ['full', 'autorenew', 'yes', 'on', '1'], true) ? 'on' : '',
            'recurringDaysBeforeExpiry'              => $val('renewal_invoice_days', '30'),
            'recurringSendExpirationNotifications'   => $on('send_expiration_reminder_email') ? $notify : '',
            'oneTimeCreateAutomaticRenewalInvoice'   => in_array($autorenew, ['full', 'autorenew', 'yes', 'on', '1'], true) ? 'on' : '',
            'oneTimeDaysBeforeExpiry'                => $val('renewal_invoice_days', '30'),
            'oneTimeSendExpirationNotifications'     => $on('send_expiration_reminder_email') ? $notify : '',
            'automaticProcessingOfRenewalOrders'     => $on('autonew_and_entrollment_renewal'),
            'additionalDaysForRenewalOrder'          => $val('no_of_renewal_days_for_renew', '0'),
            'renewOrderViaExistingOrder'             => 'on',
            'visibleRenewButtonBeforeExpiration'     => 'on',
            'visibleRenewButtonAfterExpiration'      => 'on',
            'enableCsrGenerator'                     => 'on',
        ];

        $tech = ['firstName' => 'tech_firstname', 'lastName' => 'tech_lastname', 'companyName' => 'tech_orgname', 'jobTitle' => 'tech_jobtitle',
                 'emailAddress' => 'tech_email', 'address' => 'tech_addressline1', 'city' => 'tech_city', 'stateRegion' => 'tech_region',
                 'country' => 'tech_country', 'postCode' => 'tech_postalcode', 'zipCode' => 'tech_postalcode'];
        $hasTech = false;
        foreach($tech as $unified => $legacy)
        {
            $ssl[$unified] = $val($legacy);
            $hasTech       = $hasTech || $ssl[$unified] !== '';
        }
        $ssl['useFixedTechnicalDetails'] = $hasTech && $on('use_default_tech_details') ? 'on' : '';

        $v2     = Schema::tableExists('mod_sslstore_settings_v2') ? (array)Capsule::table('mod_sslstore_settings_v2')->first() : [];
        $margin = isset($v2['profit_margin']) ? (float)$v2['profit_margin'] : 0;

        return [
            'sslSettings'     => $ssl,
            'financeSettings' => ['profitMargin' => $margin, 'rate' => 1],
            'legacy'          => [
                'live_partner_code'       => $val('live_partner_code', $v2['live_partnercode'] ?? ''),
                'api_mode'                => $val('api_mode', $v2['operationmode'] ?? ''),
                'product_sync_setting'    => $v2['product_sync_setting'] ?? '',
                'no_of_days_prior_to_expirationdate' => $val('no_of_days_prior_to_expirationdate', ''),
                'default_web_server'      => $val('default_web_server', ''),
                'send_discontinued_email' => $on('send_discontinued_email'),
            ],
        ];
    }

    // ---- Products ---- //

    /**
     * Product code of a service: tblhosting.configoption1 (often empty), else the TSS order data row,
     * else the product's configoption1 unless the product was already rewritten to the unified layout ('TSS').
     */
    protected function serviceProductCode(array $svc): string
    {
        $code = $this->productCode((string)$svc['configoption1']);
        if(in_array($code, ['tss', 'ggs'], true))
        {
            $code = ''; // product already rewritten to the unified layout during this run
        }
        if($code === '' && Schema::tableExists('mod_sslstore_orders_data'))
        {
            $code = strtolower(trim((string)Capsule::table('mod_sslstore_orders_data')->where('service_id', (int)$svc['id'])->orderBy('id', 'desc')->value('product_code')));
        }
        if($code === '')
        {
            $fromProduct = $this->productCode((string)Capsule::table('tblproducts')->where('id', (int)$svc['packageid'])->value('configoption1'));
            $code = in_array($fromProduct, ['tss', 'ggs'], true) ? '' : $fromProduct;
        }

        return $code;
    }

    protected function productCode(string $configoption1): string
    {
        $code = trim($configoption1);
        if(strpos($code, '#') !== false)
        {
            $code = substr($code, strrpos($code, '#') + 1); // < 3.3 "Name#Code"
        }

        return strtolower($code);
    }

    public function isSmimeCode(string $code): bool
    {
        if(in_array($code, self::SMIME_CODES, true))
        {
            return true;
        }
        $remote = $this->mapper && $code !== '' ? $this->mapper->forTss($code) : null;

        return $remote && strtolower((string)$remote->category) === 'smime';
    }

    /** Hardware code signing products the unified module sells through the gateway */
    public function isCodeSigningCode(string $code): bool
    {
        return in_array($code, \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::SUPPORTED_PRODUCTS['TSS'], true);
    }

    public function isUnsupportedCode(string $code): bool
    {
        if($this->isSmimeCode($code) || $this->isCodeSigningCode($code))
        {
            return false;
        }
        foreach(self::UNSUPPORTED_FRAGMENTS as $fragment)
        {
            if(strpos($code, $fragment) !== false)
            {
                return true;
            }
        }

        return false;
    }

    public function readProduct(int $pid): array
    {
        $p    = (array)Capsule::table('tblproducts')->where('id', $pid)->first();
        $code = $this->productCode((string)$p['configoption1']);
        $cfg  = $this->config();

        $included = 0;
        $others   = [];
        $csOption = null;
        foreach($this->configOptionGroupIds($pid) as $gid)
        {
            $group = Capsule::table('tblproductconfiggroups')->where('id', $gid)->first();
            foreach(Capsule::table('tblproductconfigoptions')->where('gid', $gid)->get() as $option)
            {
                if(preg_match('/\((\d+) domains? (?:are|is) included/i', (string)$option->optionname, $m))
                {
                    $included = (int)$m[1];
                }
                // code signing: the delivery option becomes the unified module's "Certificate Delivery" option
                if($this->isCodeSigningCode($code) && preg_match('/^Certificate Delivery Method/i', (string)$option->optionname))
                {
                    $csOption = ['id' => (int)$option->id, 'gid' => (int)$gid, 'optionname' => (string)$option->optionname];
                    continue;
                }
                if(preg_match('/^(Additional Servers|CSR Domain|Site Seal|Certificate Delivery Method)/i', (string)$option->optionname))
                {
                    $others[] = ['id' => (int)$option->id, 'gid' => (int)$gid, 'optionname' => (string)$option->optionname];
                }
            }
        }

        $warnings = [];
        if($others)
        {
            $warnings[] = 'TSS-only configurable options are kept but ignored by the unified module: ' . implode(', ', array_map(function($o) { return explode('|', $o['optionname'])[0]; }, $others));
        }
        if($this->isUnsupportedCode($code))
        {
            $warnings[] = 'Product type not supported by the unified module (web security / code signing / document signing / scanner)';
        }

        return [
            'pid'                   => $pid,
            'name'                  => (string)$p['name'],
            'legacy_product_ref'    => $code,
            'remote_product'        => $this->mapper ? $this->mapper->forTss($code) : null,
            'term_months'           => 12,
            'included_san'          => $included,
            'included_san_wildcard' => 0,
            'price_auto'            => !empty($cfg['update_prices_on_sync']) && strtolower($cfg['update_prices_on_sync']) !== 'no',
            'san_options'           => $this->sanOptions($pid, ['sans' => '/^Additional SAN(\s*\||\s*\(|$)/i', 'sans_wildcard' => '/^Additional WildCard SAN/i']),
            'other_options'         => $others,
            'cs_delivery_option'    => $csOption,
            'unsupported'           => $this->isUnsupportedCode($code),
            'warnings'              => $warnings,
        ];
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
        $code  = $this->serviceProductCode($svc);
        $rec   = [
            'service_id'   => $serviceId,
            'client_id'    => (int)$svc['userid'],
            'product_id'   => (int)$svc['packageid'],
            'ssl_order_id' => $order ? (int)$order['id'] : 0,
            'product_code' => $code,
            'kind'         => 'ssl',
            'warnings'     => [],
            'skip_reason'  => null,
            'preordered'   => false,
        ];

        if($this->isUnsupportedCode($code))
        {
            $rec['kind']        = 'unsupported';
            $rec['skip_reason'] = 'Product type "' . $code . '" is not supported by the unified module';

            return $rec;
        }
        if(!$order)
        {
            $rec['skip_reason'] = $svc['domainstatus'] === 'Pending' ? 'Unpaid: the SSL order is created by the unified module when the invoice is paid' : 'No SSL order row for this service';

            return $rec;
        }
        if(!empty($order['addon_id']))
        {
            $rec['kind']        = 'unsupported';
            $rec['skip_reason'] = 'Sold as a WHMCS product addon (addon_id ' . $order['addon_id'] . '); product addons are not supported by the unified module';

            return $rec;
        }

        $rec['legacy_status'] = (string)$order['status'];
        $cd                   = Configdata::decode($order['configdata']);

        if(in_array($code, self::SUBSCRIPTION_CODES, true) || in_array($code, self::ACME_CODES, true) || Schema::count('tss_subscription', ['service_id' => $serviceId]) > 0)
        {
            return $this->readSubscription($rec, $order, $code);
        }
        if($this->isSmimeCode($code))
        {
            return $this->readSmime($rec, $svc, $order, $code);
        }
        if($this->isCodeSigningCode($code))
        {
            return $this->readCodeSigning($rec, $svc, $order, $code);
        }

        return $this->readSsl($rec, $svc, $order, $cd, $code);
    }

    /**
     * S/MIME: the TSS module places an "invite" order when the service is created and the client enrols on the
     * certificate authority's page (TinyOrderLink). The order keeps its ID (gateway item ID = TheSSLStore order ID).
     * The current state and the enrolment link are read from TheSSLStore (order/status); the legacy tables are the
     * fallback. Written as the unified module's "smime" request row (SmimeService).
     */
    protected function readSmime(array $rec, array $svc, array $order, string $code): array
    {
        $rec['kind'] = 'smime';
        $remote      = $this->mapper ? $this->mapper->forTss($code) : null;
        $rec['remote_product_id'] = $remote ? (string)$remote->remoteId : $code;
        if(!$remote)
        {
            $rec['warnings'][] = 'Gateway product not found for TheSSLStore product code "' . $code . '"';
        }

        $remoteId = trim((string)$order['remoteid']);
        if($remoteId === '' || $remoteId === '0')
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }

        $od = Schema::tableExists('mod_sslstore_orders_data') ? Capsule::table('mod_sslstore_orders_data')->where('service_id', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        $od = $od ? (array)$od : [];

        $smime = [
            'orderId' => (int)$remoteId, 'itemId' => (int)$remoteId, 'status' => '', 'emails' => [], 'vendorOrderId' => (string)($od['vendor_order_id'] ?? ''),
            'validFrom' => '', 'validTill' => '', 'term' => (int)($od['no_of_months'] ?? 12) ?: 12, 'csrProvided' => false,
            'orderedAt' => (string)($od['order_placed_date'] ?? ''), 'previous' => [], 'remindersSent' => [2, 7], 'migrated' => true,
        ];

        $tss = $this->tssOrderStatus($remoteId);
        if($tss !== null)
        {
            $major = strtolower(trim((string)($tss['OrderStatus']['MajorStatus'] ?? '')));
            $map   = ['initial' => 'invited', 'pending' => 'pending', 'active' => 'active', 'cancelled' => 'cancelled', 'canceled' => 'cancelled',
                      'rejected' => 'rejected', 'expired' => 'expired', 'revoked' => 'revoked', 'refunded' => 'refunded'];
            $smime['status'] = $map[$major] ?? 'processing';
            if($smime['status'] === 'invited' && !empty($tss['TinyOrderLink']))
            {
                // the client has not opened the enrolment link yet: it stays valid after the migration
                $smime['enrollmentLink'] = (string)$tss['TinyOrderLink'];
            }
            $commonName = strtolower(trim((string)($tss['CommonName'] ?? '')));
            if(strpos($commonName, '@') !== false)
            {
                $smime['emails'] = [['email' => $commonName, 'approved' => $smime['status'] === 'active']];
            }
            if(!empty($tss['VendorOrderID']))
            {
                $smime['vendorOrderId'] = (string)$tss['VendorOrderID'];
            }
            if($smime['status'] === 'active')
            {
                $smime['validFrom'] = $this->tssDate($tss['CertificateStartDate'] ?? '');
                $smime['validTill'] = $this->tssDate($tss['CertificateEndDate'] ?? '');
            }
        }
        else
        {
            $rec['warnings'][] = 'TheSSLStore order status could not be read; the state comes from the legacy tables (the first refresh corrects it)';
            $smime['status'] = $order['status'] === self::STATUS_AWAITING ? 'invited' : $this->normalizeStatus((string)($od['ssl_status'] ?? ''), (string)$order['status']);
        }

        if($smime['status'] === 'invited')
        {
            $rec['preordered'] = true;
            $rec['warnings'][] = empty($smime['enrollmentLink'])
                ? 'S/MIME order ' . $remoteId . ' waits for the client\'s enrolment; the enrolment link is in the e-mail the client received'
                : 'S/MIME order ' . $remoteId . ' waits for the client\'s enrolment; the client area shows the enrolment link';
        }
        if(!$smime['emails'] && $smime['status'] !== 'invited')
        {
            $rec['warnings'][] = 'S/MIME email address not known yet; it is read from the gateway by the first refresh';
        }

        $rec['remote_id']      = $remoteId;
        $rec['request_status'] = $smime['status'];
        $rec['target_status']  = $smime['status'] === 'active' ? 'Completed'
            : (in_array($smime['status'], ['cancelled', 'rejected', 'revoked', 'refunded'], true) ? 'Cancelled' : ($smime['status'] === 'expired' ? 'Expired' : self::STATUS_SUBMITTED));
        $rec['smime']  = $smime;
        $rec['prices'] = [
            'api_price'   => (float)($od['purchase_amt'] ?? 0),
            'whmcs_price' => (float)$svc['amount'],
            'invoiceid'   => (int)($od['invoice_id'] ?? 0),
        ];

        return $rec;
    }

    /**
     * Code signing: like S/MIME an "invite" order placed when the service is created; the client enrols on the
     * certificate authority's page (TinyOrderLink). The delivery comes from CSTokenDeliveryMethod (else from the
     * service's "Certificate Delivery Method" option). Written as the unified module's "codesigning" request row
     * (CodeSigningService). Renewals are new orders in the unified module.
     */
    protected function readCodeSigning(array $rec, array $svc, array $order, string $code): array
    {
        $cs          = \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::class;
        $rec['kind'] = 'codesigning';
        $remote      = $this->mapper ? $this->mapper->forTss($code) : null;
        $rec['remote_product_id'] = $remote ? (string)$remote->remoteId : $code;
        if(!$remote)
        {
            $rec['warnings'][] = 'Gateway product not found for TheSSLStore product code "' . $code . '"';
        }

        $remoteId = trim((string)$order['remoteid']);
        if($remoteId === '' || $remoteId === '0')
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }

        $od = Schema::tableExists('mod_sslstore_orders_data') ? Capsule::table('mod_sslstore_orders_data')->where('service_id', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        $od = $od ? (array)$od : [];

        // the delivery the client chose with the order (legacy option name)
        $optionValue = Capsule::table('tblhostingconfigoptions')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
            ->where('tblhostingconfigoptions.relid', $rec['service_id'])
            ->where('tblproductconfigoptions.optionname', 'like', 'Certificate Delivery Method%')
            ->value('tblproductconfigoptionssub.optionname');
        $delivery = $cs::legacyDelivery(explode('|', (string)$optionValue, 2)[0]);

        $data = [
            'orderId' => (int)$remoteId, 'itemId' => (int)$remoteId, 'status' => '', 'vendorOrderId' => (string)($od['vendor_order_id'] ?? ''),
            'validFrom' => '', 'validTill' => '', 'term' => (int)($od['no_of_months'] ?? 12) ?: 12, 'method' => '', 'tokenModel' => '', 'shipping' => '',
            'csrProvided' => false, 'organization' => (string)$svc['domain'], 'orderedAt' => (string)($od['order_placed_date'] ?? ''), 'previous' => [], 'migrated' => true,
        ];

        $tss = $this->tssOrderStatus($remoteId);
        if($tss !== null)
        {
            $major = strtolower(trim((string)($tss['OrderStatus']['MajorStatus'] ?? '')));
            $map   = ['initial' => 'invited', 'pending' => 'pending', 'active' => 'active', 'cancelled' => 'cancelled', 'canceled' => 'cancelled',
                      'rejected' => 'rejected', 'expired' => 'expired', 'revoked' => 'revoked', 'refunded' => 'refunded'];
            $data['status'] = $map[$major] ?? 'processing';
            if($data['status'] === 'invited' && !empty($tss['TinyOrderLink']))
            {
                // the client has not enrolled yet: the enrolment link stays valid after the migration
                $data['enrollmentLink'] = (string)$tss['TinyOrderLink'];
            }
            if(isset($tss['CSTokenDeliveryMethod']) && $tss['CSTokenDeliveryMethod'] !== null && $cs::legacyDelivery($tss['CSTokenDeliveryMethod']) !== null)
            {
                $delivery = $cs::legacyDelivery($tss['CSTokenDeliveryMethod']);
            }
            // DigiCert: the page to set up the shipped token
            if(!empty($tss['CSTokenSetUpLink']) && empty($tss['IsCSTokenSet']))
            {
                $data['tokenSetupLink'] = (string)$tss['CSTokenSetUpLink'];
            }
            if(!empty($tss['OrganizationInfo']['OrganizationName']))
            {
                $data['organization'] = (string)$tss['OrganizationInfo']['OrganizationName'];
            }
            if(!empty($tss['VendorOrderID']))
            {
                $data['vendorOrderId'] = (string)$tss['VendorOrderID'];
            }
            if($data['status'] === 'active')
            {
                $data['validFrom'] = $this->tssDate($tss['CertificateStartDate'] ?? '');
                $data['validTill'] = $this->tssDate($tss['CertificateEndDate'] ?? '');
            }
        }
        else
        {
            $rec['warnings'][] = 'TheSSLStore order status could not be read; the state comes from the legacy tables (the first refresh corrects it)';
            $data['status'] = $order['status'] === self::STATUS_AWAITING ? 'invited' : $this->normalizeStatus((string)($od['ssl_status'] ?? ''), (string)$order['status']);
        }

        if($delivery !== null)
        {
            $parsed           = $cs::parseProvisioningValue($delivery);
            $data['method']   = $parsed['method'];
            $data['shipping'] = (string)$parsed['shipping'];
        }
        if($data['status'] === 'invited')
        {
            $rec['preordered'] = true;
            $rec['warnings'][] = empty($data['enrollmentLink'])
                ? 'Code signing order ' . $remoteId . ' waits for the client\'s enrolment; the enrolment link is in the e-mail the client received'
                : 'Code signing order ' . $remoteId . ' waits for the client\'s enrolment; the client area shows the enrolment link';
        }

        $rec['remote_id']      = $remoteId;
        $rec['request_status'] = $data['status'];
        $rec['target_status']  = $data['status'] === 'active' ? 'Completed'
            : (in_array($data['status'], ['cancelled', 'rejected', 'revoked', 'refunded'], true) ? 'Cancelled' : ($data['status'] === 'expired' ? 'Expired' : self::STATUS_SUBMITTED));
        $rec['codesigning'] = $data;
        $rec['prices']      = [
            'api_price'   => (float)($od['purchase_amt'] ?? 0),
            'whmcs_price' => (float)$svc['amount'],
            'invoiceid'   => (int)($od['invoice_id'] ?? 0),
        ];

        return $rec;
    }

    /** @var array TheSSLStore order/status answers of this run */
    protected $tssStatusCache = [];

    /**
     * TheSSLStore REST order/status with the TSS module's credentials (null when it can not be read)
     */
    protected function tssOrderStatus(string $orderId): ?array
    {
        if(array_key_exists($orderId, $this->tssStatusCache))
        {
            return $this->tssStatusCache[$orderId];
        }

        $result = null;
        try
        {
            $c    = $this->readCredentials();
            $live = $c['OperationMode'] === 'live';
            $code = $live ? $c['PartnerCode'] : $c['TestPartnerCode'];
            $key  = $live ? $c['AuthToken'] : $c['TestAuthToken'];
            if($code !== '' && $key !== '' && function_exists('curl_init'))
            {
                $ch = curl_init(($live ? 'https://api.thesslstore.com' : 'https://sandbox-wbapi.thesslstore.com') . '/rest/order/status');
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => json_encode(['AuthRequest' => ['PartnerCode' => $code, 'AuthToken' => $key], 'TheSSLStoreOrderID' => $orderId]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
                ]);
                $answer = json_decode((string)curl_exec($ch), true);
                curl_close($ch);
                if(is_array($answer) && empty($answer['AuthResponse']['isError']) && isset($answer['OrderStatus']))
                {
                    $result = $answer;
                }
            }
        }
        catch(\Throwable $e)
        {
        }

        return $this->tssStatusCache[$orderId] = $result;
    }

    /** TheSSLStore date ("11/11/2027 12:47:46 PM", 1/1/1900 = none) -> 'Y-m-d H:i:s' */
    protected function tssDate($value): string
    {
        $time = $value ? strtotime((string)$value) : false;

        return $time && (int)date('Y', $time) > 2000 ? date('Y-m-d H:i:s', $time) : '';
    }

    protected function readSsl(array $rec, array $svc, array $order, array $cd, string $code): array
    {
        $remoteProduct = $this->mapper ? $this->mapper->forTss($code) : null;
        $rec['remote_product_id'] = $remoteProduct ? (string)$remoteProduct->remoteId : '';
        if(!$remoteProduct)
        {
            $rec['warnings'][] = 'Gateway product not found for TheSSLStore product code "' . $code . '"';
        }

        $remoteId = trim((string)$order['remoteid']);
        $od       = Schema::tableExists('mod_sslstore_orders_data') ? Capsule::table('mod_sslstore_orders_data')->where('service_id', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        $od       = $od ? (array)$od : [];

        if($order['status'] === self::STATUS_AWAITING)
        {
            // invited at TSS already (token exists there) when a store order id is present
            $rec['preordered']     = $remoteId !== '' && $remoteId !== '0';
            $rec['remote_id']      = $rec['preordered'] ? $remoteId : '';
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['request_status'] = 'pending';
            if($rec['preordered'])
            {
                $rec['warnings'][] = 'Pre-ordered at TheSSLStore (order ' . $remoteId . '); needs the enrol-existing-order flow when configured';
            }

            return $rec;
        }
        if($remoteId === '' || $remoteId === '0')
        {
            $rec['skip_reason'] = 'No TheSSLStore order id on a configured service';

            return $rec;
        }

        $vendorStatus  = (string)($od['ssl_status'] ?? '');
        $gatewayStatus = $this->normalizeStatus($vendorStatus ?: (string)($od['issuance_status'] ?? ''), (string)$order['status']);
        if(!empty($od['expiration_date']) && $od['expiration_date'] !== '0000-00-00 00:00:00' && strtotime($od['expiration_date']) < time() && $gatewayStatus === 'active')
        {
            $gatewayStatus = 'expired';
        }
        $rec['remote_id']      = $remoteId;
        $rec['request_status'] = $gatewayStatus;
        $rec['target_status']  = $this->targetSslStatus((string)$order['status'], $gatewayStatus);

        $commonName = strtolower((string)Configdata::get($cd, 'domain', $od['common_name'] ?? $svc['domain']));
        $csr        = Configdata::pem(Configdata::get($cd, 'csr', ''));
        $f          = (array)($cd['fields'] ?? []);
        $dcvMap     = [];
        foreach((array)($f['sslDCVAuthMethod'] ?? []) as $d => $m)
        {
            $dcvMap[strtolower(Configdata::unstar((string)$d))] = $this->cnMethod((string)$m);
        }
        $approvers = Configdata::get($cd, 'approveremail', []);
        $approverFor = function($domain) use ($approvers) {
            if(is_array($approvers))
            {
                foreach($approvers as $d => $e) { if(strtolower(Configdata::unstar((string)$d)) === $domain) return (string)$e; }
                return '';
            }

            return (string)$approvers;
        };
        $methodFor = function($domain) use ($dcvMap, $od, $approverFor) {
            $m = $dcvMap[$domain] ?? $this->cnMethod((string)($od['dcv_method'] ?? 'email'));
            if($m === 'email' && strpos($approverFor($domain), '@') !== false)
            {
                return $approverFor($domain);
            }

            return $m ?: 'email';
        };

        $sans = [];
        foreach(array_merge(Configdata::domainList($f['sslAdditionalSan'] ?? ''), Configdata::domainList($f['sslAdditionalWildCardSan'] ?? '')) as $name)
        {
            if($name !== $commonName)
            {
                $sans[] = ['name' => $name, 'validation_method' => $methodFor($name)];
            }
        }

        $admin = ['first_name' => Configdata::get($cd, 'firstname', ''), 'last_name' => Configdata::get($cd, 'lastname', ''), 'title' => Configdata::get($cd, 'jobtitle', ''),
                  'email' => Configdata::get($cd, 'email', ''), 'phone' => Configdata::get($cd, 'phonenumber', '')];
        $tech  = ['first_name' => Configdata::get($f, 'TechFirstName', $admin['first_name']), 'last_name' => Configdata::get($f, 'TechLastName', $admin['last_name']),
                  'title' => Configdata::get($f, 'TechTitle', $admin['title']), 'email' => Configdata::get($f, 'TechEmail', $admin['email']), 'phone' => Configdata::get($f, 'TechPhone', $admin['phone'])];
        $org   = ['name' => Configdata::get($cd, 'orgname', Configdata::get($cd, 'csrcompany', '')), 'division' => Configdata::get($f, 'sslOrgDivision', Configdata::get($cd, 'csrdivision', '')),
                  'duns' => Configdata::get($f, 'sslOrgDuns', ''), 'address' => Configdata::get($cd, 'address1', ''), 'city' => Configdata::get($cd, 'city', Configdata::get($cd, 'csrcity', '')),
                  'region' => Configdata::get($cd, 'state', Configdata::get($cd, 'csrstate', '')), 'country' => Configdata::get($cd, 'country', Configdata::get($cd, 'csrcountry', '')),
                  'postalcode' => Configdata::get($cd, 'postcode', ''), 'phone' => Configdata::get($f, 'sslOrgPhone', '')];

        $term    = (int)($od['no_of_months'] ?? 12) ?: 12;
        $payload = $this->buildOrderPayload($rec['remote_product_id'], $term, $csr, $commonName, $methodFor($commonName), $sans, $this->buildContacts($admin, $tech, $org));
        $rec['order_payload'] = $payload;
        if($csr === '')
        {
            $rec['warnings'][] = 'No CSR stored; reissue will require a new CSR';
        }

        $validFrom = !empty($od['start_date']) && $od['start_date'] !== '0000-00-00 00:00:00' ? substr($od['start_date'], 0, 10) : '';
        $validTill = !empty($od['expiration_date']) && $od['expiration_date'] !== '0000-00-00 00:00:00' ? substr($od['expiration_date'], 0, 10) : '';
        $subEnd    = !empty($od['order_expiration_date']) && $od['order_expiration_date'] !== '0000-00-00 00:00:00' ? substr($od['order_expiration_date'], 0, 10) : $validTill;

        $domains = array_merge([['name' => $commonName, 'method' => $this->cnMethod($methodFor($commonName)), 'approved' => $gatewayStatus === 'active']],
            array_map(function($s) use ($gatewayStatus) { return ['name' => $s['name'], 'method' => $this->cnMethod($s['validation_method']), 'approved' => $gatewayStatus === 'active']; }, $sans));

        $rec['certificate'] = $this->buildCertificate([
            'status' => $gatewayStatus, 'item_id' => (int)$remoteId, 'order_id' => (int)$remoteId,
            'vendor_order_id' => (string)($od['vendor_order_id'] ?? ''), 'remote_product_id' => $rec['remote_product_id'],
            'csr' => $csr, 'common_name' => $commonName, 'domains' => $domains, 'crt' => '', 'ca' => '',
            'valid_from' => $validFrom, 'valid_till' => $validTill, 'subscription_begin' => $validFrom, 'subscription_end' => $subEnd,
            'order_date' => (string)($od['order_placed_date'] ?? $order['completiondate']), 'order_payload' => $payload,
            'api_price' => (float)($od['purchase_amt'] ?? 0),
        ]);
        $rec['warnings'][] = 'Certificate files are not stored by the TSS module; downloads appear after the first gateway sync';

        $rec['prices'] = [
            'api_price'   => (float)($od['purchase_amt'] ?? 0),
            'whmcs_price' => (float)($od['sales_amt'] ?? $svc['amount']),
            'invoiceid'   => (int)($od['invoice_id'] ?? 0),
        ];

        // AutoInstall
        $rec['private_key'] = '';
        $rec['autoinstall'] = null;
        $rec['queue']       = [];
        $ai = Schema::tableExists('mod_autoinstallssl_orders') ? Capsule::table('mod_autoinstallssl_orders')->where('serviceid', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        if($ai && in_array($gatewayStatus, ['active', 'pending', 'processing'], true))
        {
            $ai        = (array)$ai;
            $domainMap = json_decode((string)$ai['domain'], true) ?: [];
            $installDomain = strtolower(preg_replace('/^\*\./', '', (string)(array_keys($domainMap)[0] ?? $commonName)));
            $cpanelUser    = (string)($ai['hosting_profile'] ?: reset($domainMap));
            $cpanel        = $this->cpanelServiceByUser($rec['client_id'], $cpanelUser) ?: $this->cpanelService($rec['client_id'], $installDomain);

            if(!$cpanel)
            {
                $rec['warnings'][] = 'AutoInstall: no Active cPanel account "' . $cpanelUser . '" of this client; migrated as manual install';
            }
            else
            {
                $status    = strtoupper((string)$ai['status']);
                $installed = in_array($status, ['INSTALLED', 'RENEWAL_COMPLETE'], true);
                $disabled  = in_array($status, ['EXPIRED', 'REVOKED'], true);
                $failed    = in_array($status, ['REISSUE_FAILED', 'ERROR'], true);
                $rec['autoinstall'] = [
                    'install_type'       => 'autoinstall',
                    'domain'             => $installDomain,
                    'common_name'        => $commonName,
                    'dcv_method'         => $this->cnMethod((string)$ai['dcv_method'] ?: 'email'),
                    'dns_fallback_email' => (string)$ai['dcv_email'],
                    'is_wildcard'        => (bool)$ai['is_wildcard'],
                    'created_at'         => (string)$ai['created_at'] ?: date('Y-m-d H:i:s'),
                    'installed_crt_hash' => '',                     // computed after the first sync fetches the files
                    'installed_at'       => $installed ? (string)($ai['issued_at'] ?: $ai['updated_at']) : '',
                    'api_order_id'       => (int)$remoteId,
                    'valid_till'         => !empty($ai['cert_expiry']) && $ai['cert_expiry'] !== '0000-00-00 00:00:00' ? substr($ai['cert_expiry'], 0, 10) : $validTill,
                    'subscription_end'   => !empty($ai['order_expiry']) && $ai['order_expiry'] !== '0000-00-00 00:00:00' ? substr($ai['order_expiry'], 0, 10) : $subEnd,
                    'last_error'         => '',
                    'cpanel_service_id'  => (int)$cpanel['id'],
                    'cpanel_username'    => (string)$cpanel['username'],
                    'legacy_pvtkey_id'   => (string)$ai['pvtkey_id'],
                    'status'             => $disabled ? 'disabled' : ($failed ? 'failed' : ($installed ? 'installed' : 'awaiting_validation')),
                    'legacy_status'      => $status,
                ];
                if(!$installed && !$disabled)
                {
                    $rec['queue'][] = ['type' => 'validate', 'data' => ['domain' => $installDomain, 'api_order_id' => (int)$remoteId, 'is_wildcard' => (bool)$ai['is_wildcard'], 'dns_fallback_email' => (string)$ai['dcv_email']]];
                }
                $rec['warnings'][] = 'AutoInstall private key is kept in cPanel (key id ' . $ai['pvtkey_id'] . '); fetched during migration, otherwise re-keyed at the next reissue';
            }
        }

        return $rec;
    }

    protected function readSubscription(array $rec, array $order, string $code): array
    {
        $sub = Schema::tableExists('tss_subscription') ? Capsule::table('tss_subscription')->where('service_id', $rec['service_id'])->orderBy('id', 'desc')->first() : null;
        $isAcme = in_array($code, self::ACME_CODES, true) || ($sub && strtolower((string)$sub->install_method) === 'acme');
        $rec['kind'] = $isAcme ? 'acme' : 'ais';

        if(!$sub)
        {
            $rec['target_status']  = self::STATUS_AWAITING;
            $rec['remote_id']      = '';
            $rec['request_status'] = 'pending';

            return $rec;
        }
        $sub    = (array)$sub;
        $subId  = (int)$sub['subscription_id'] ?: (int)$sub['order_id'];
        $status = strtolower((string)$sub['status']);
        $statusMap = ['active' => 'active', 'pending-enrollment' => 'pending', 'pending' => 'pending', 'cancelled' => 'cancelled', 'canceled' => 'cancelled', 'suspended' => 'cancelled', 'expired' => 'expired'];
        $uStatus   = $statusMap[$status] ?? 'pending';

        $rec['remote_id']      = (string)$subId;
        $rec['request_status'] = $uStatus;
        $rec['target_status']  = $uStatus === 'active' ? 'Completed' : ($order['status'] === self::STATUS_AWAITING ? self::STATUS_AWAITING : self::STATUS_SUBMITTED);

        $periodStart = !empty($sub['subscription_start_date']) && $sub['subscription_start_date'] !== '0000-00-00 00:00:00' ? substr($sub['subscription_start_date'], 0, 10) : null;
        $periodEnd   = !empty($sub['subscription_end_date']) && $sub['subscription_end_date'] !== '0000-00-00 00:00:00' ? substr($sub['subscription_end_date'], 0, 10) : null;
        $renewal     = !empty($sub['next_bill_date']) && $sub['next_bill_date'] !== '0000-00-00 00:00:00' ? substr($sub['next_bill_date'], 0, 10) : $periodEnd;

        if($isAcme)
        {
            $eab     = Schema::tableExists('tss_acme_eab') ? (array)(Capsule::table('tss_acme_eab')->where('subscription_id', (string)$sub['subscription_id'])->first() ?: []) : [];
            $domains = [];
            foreach((array)(json_decode((string)$sub['acme_domains'], true) ?: []) as $d)
            {
                $name = strtolower((string)($d['DomainName'] ?? $d['domain'] ?? ''));
                if($name !== '')
                {
                    $domains[] = ['domain' => $name, 'domain_type' => strpos($name, '*.') === 0 ? 'wildcard' : 'single', 'status' => 'added', 'added_at' => $sub['created_at'] ?? null, 'removed_at' => null];
                }
            }
            $rec['subscription'] = ['acme' => [
                'client_id' => $rec['client_id'], 'api_order_id' => $subId, 'acme_id' => $subId, 'status' => $uStatus,
                'acme_account_id' => (string)($eab['vendor_account_id'] ?? ''), 'eab_kid' => (string)($eab['eab_kid'] ?? ''),
                'eab_hmac_key' => (string)($eab['eab_hmac_key'] ?? ''), 'server_url' => (string)($eab['acme_directory_url'] ?? ''),
                'period_start' => $periodStart, 'period_end' => $periodEnd, 'renewal_date' => $renewal,
                'auto_renew' => (int)$sub['auto_renew'], 'cancelled_at' => null, 'domains' => $domains,
                'limits' => ['single' => (int)$sub['limit_standard'], 'wildcard' => (int)$sub['limit_wildcard']],
            ]];
            $rec['configdata'] = ['subscription' => true, 'ssl_status' => $uStatus, 'begin_date' => $periodStart, 'end_date' => $periodEnd, 'renewal_date' => $renewal];
        }
        else
        {
            $rec['subscription'] = ['ais' => [
                'client_id' => $rec['client_id'], 'api_order_id' => $subId, 'ais_item_id' => $subId, 'status' => $uStatus,
                'login_sso_link' => '', 'manage_sso_link' => '', 'autoinstall_status' => (string)($sub['certificate_status'] ?? ''),
                'period_start' => $periodStart, 'period_end' => $periodEnd, 'renewal_date' => $renewal,
                'auto_renew' => (int)$sub['auto_renew'], 'cancelled_at' => null,
                'limits' => ['single' => (int)$sub['limit_standard'], 'wildcard' => (int)$sub['limit_wildcard']],
            ]];
            $rec['configdata'] = ['ssl_status' => $uStatus];
            $rec['warnings'][] = 'AIS SSO links are refreshed from the gateway on first open';
        }

        return $rec;
    }

    protected function cpanelServiceByUser(int $clientId, string $username): ?array
    {
        if($username === '')
        {
            return null;
        }
        $row = Capsule::table('tblhosting')
            ->select('tblhosting.id', 'tblhosting.domain', 'tblhosting.username', 'tblhosting.server', 'tblhosting.domainstatus')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->whereIn('tblproducts.servertype', ['cpanel', 'cpanelExtended', 'cpanelextended', 'cpanel_extended'])
            ->where('tblhosting.userid', $clientId)->where('tblhosting.username', $username)->where('tblhosting.domainstatus', 'Active')->first();

        return $row ? (array)$row : null;
    }

    // ---- Detection counts ---- //

    public function counts(): array
    {
        $serverType  = $this->serverType();
        $products    = Capsule::table('tblproducts')->selectRaw('COUNT(*) AS c, SUM(hidden) AS h, SUM(retired) AS r')->where('servertype', $serverType)->first();
        $unsupported = 0;
        foreach(Capsule::table('tblproducts')->where('servertype', $serverType)->get() as $p)
        {
            if($this->isUnsupportedCode($this->productCode((string)$p->configoption1)))
            {
                $unsupported++;
            }
        }
        $addonOrders = Schema::columnExists('tblsslorders', 'addon_id') ? (int)Capsule::table('tblsslorders')->whereIn('module', $this->orderModules())->where('addon_id', '>', 0)->count() : 0;

        return [
            'products'                => ['total' => (int)$products->c, 'hidden' => (int)$products->h, 'retired' => (int)$products->r],
            'services'                => $this->countBy('tblhosting', 'domainstatus', function($q) use ($serverType) { $q->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')->where('tblproducts.servertype', $serverType); }),
            'orders'                  => $this->countBy('tblsslorders', 'status', function($q) { $q->whereIn('module', $this->orderModules()); }),
            'acme'                    => Schema::tableExists('tss_subscription') ? Schema::count('tss_subscription', ['install_method' => 'acme']) : 0,
            'ais'                     => Schema::tableExists('tss_subscription') ? (int)Capsule::table('tss_subscription')->where('install_method', '<>', 'acme')->count() : 0,
            'autoinstall'             => Schema::count('mod_autoinstallssl_orders'),
            'queue_open'              => Schema::tableExists('mod_autoinstallssl_orders') ? Schema::count('mod_autoinstallssl_orders', ['status' => ['PENDING_VALIDATION', 'PENDING_ISSUANCE_INSTALL', 'RENEWAL_PENDING']]) : 0,
            'unconfigured'            => Schema::count('tblsslorders', ['module' => $this->orderModules(), 'status' => self::STATUS_AWAITING]),
            'unconfigured_preordered' => (int)Capsule::table('tblsslorders')->whereIn('module', $this->orderModules())->where('status', self::STATUS_AWAITING)->where('remoteid', '<>', '')->where('remoteid', '<>', '0')->count(),
            'unpaid'                  => (int)Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')->where('tblproducts.servertype', $serverType)->where('tblhosting.domainstatus', 'Pending')->count(),
            'unsupported'             => ['products' => $unsupported, 'addon_orders' => $addonOrders],
            'other_ssl_modules'       => $this->otherSslModules(),
        ];
    }
}
