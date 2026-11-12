<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Readers;

use ModulesGarden\TSSGGSModule\App\Migration\Support\Schema;
use WHMCS\Database\Capsule;

/**
 * Shared helpers for the legacy readers: WHMCS-side lookups and the gateway-shaped record builders.
 */
abstract class AbstractReader implements LegacyReaderInterface
{
    /** SSL modules of other vendors found in tblsslorders.module, reported as "not touched" */
    const KNOWN_OTHER_MODULES = ['gogetssl', 'enomssl', 'resellerclubssl', 'globalsignssl', 'marketconnect', 'TSSGGSModule'];

    const STATUS_AWAITING  = 'Awaiting Configuration';
    const STATUS_SUBMITTED = 'Configuration Submitted';

    protected $versionCache = false;

    /** @var array product id => configoption values the legacy module had (set once the products are rewritten) */
    protected $legacyProductOptions = [];

    /**
     * The products are rewritten to the unified layout before their services are read: the services must still be
     * read with the legacy values (product id / code, term), exactly as the dry run reads them.
     */
    public function setLegacyProductOptions(array $byProductId): void
    {
        $this->legacyProductOptions = $byProductId;
    }

    // ---- WHMCS lookups ---- //

    public function version(): ?string
    {
        if($this->versionCache === false)
        {
            $this->versionCache = Capsule::table('tbladdonmodules')->where('module', $this->addonName())->where('setting', 'version')->value('value') ?: null;
        }

        return $this->versionCache;
    }

    public function isAddonActive(): bool
    {
        $active = (string)Capsule::table('tblconfiguration')->where('setting', 'ActiveAddonModules')->value('value');

        return in_array($this->addonName(), array_map('trim', explode(',', $active)), true);
    }

    public function filesPresent(): bool
    {
        $root = dirname(__DIR__, 5); // .../modules

        return is_dir($root . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . $this->addonName())
            || is_dir($root . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . $this->serverType());
    }

    public function productIds(): array
    {
        return array_map('intval', Capsule::table('tblproducts')->where('servertype', $this->serverType())->orderBy('id')->pluck('id')->all());
    }

    public function serviceIds(): array
    {
        return array_map('intval', Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', $this->serverType())
            ->orderBy('tblhosting.id')->pluck('tblhosting.id')->all());
    }

    protected function service(int $serviceId): ?array
    {
        $row = Capsule::table('tblhosting')
            ->select('tblhosting.*', 'tblproducts.servertype', 'tblproducts.name AS product_name', 'tblproducts.paytype',
                'tblproducts.configoption1', 'tblproducts.configoption2', 'tblproducts.configoption3', 'tblproducts.configoption4',
                'tblproducts.configoption5', 'tblproducts.configoption6', 'tblproducts.configoption7', 'tblproducts.configoption8',
                'tblproducts.configoption13', 'tblproducts.configoption23', 'tblproducts.configoption24')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', $serviceId)->first();
        if(!$row)
        {
            return null;
        }
        $row = (array)$row;
        foreach($this->legacyProductOptions[(int)$row['packageid']] ?? [] as $column => $value)
        {
            if(array_key_exists($column, $row))
            {
                $row[$column] = $value;
            }
        }

        return $row;
    }

    protected function sslOrder(int $serviceId): ?array
    {
        $row = Capsule::table('tblsslorders')->where('serviceid', $serviceId)->whereIn('module', $this->orderModules())->orderBy('id', 'desc')->first();

        return $row ? (array)$row : null;
    }

    protected function client(int $clientId): ?array
    {
        $row = Capsule::table('tblclients')->where('id', $clientId)->first();

        return $row ? (array)$row : null;
    }

    /**
     * The client's Active cPanel hosting account that serves $domain, or by hosting id when known.
     * Mirrors what the unified Cpanel lookup needs: Active + servertype cpanel/cpanelExtended + same client.
     */
    protected function cpanelService(int $clientId, string $domain, int $hostingId = 0): ?array
    {
        $query = Capsule::table('tblhosting')
            ->select('tblhosting.id', 'tblhosting.domain', 'tblhosting.username', 'tblhosting.server', 'tblhosting.domainstatus', 'tblproducts.servertype')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->whereIn('tblproducts.servertype', ['cpanel', 'cpanelExtended', 'cpanelextended', 'cpanel_extended'])
            ->where('tblhosting.userid', $clientId)
            ->where('tblhosting.domainstatus', 'Active');

        if($hostingId > 0)
        {
            $byId = (clone $query)->where('tblhosting.id', $hostingId)->first();
            if($byId)
            {
                return (array)$byId;
            }
        }

        $domain = strtolower(preg_replace('/^(\*\.|www\.)/', '', trim($domain)));
        foreach($query->get() as $row)
        {
            $candidate = strtolower(preg_replace('/^www\./', '', (string)$row->domain));
            if($candidate === $domain || substr($domain, -strlen('.' . $candidate)) === '.' . $candidate)
            {
                return (array)$row;
            }
        }

        return null;
    }

    protected function configOptionGroupIds(int $pid): array
    {
        return array_map('intval', Capsule::table('tblproductconfiglinks')->where('pid', $pid)->pluck('gid')->all());
    }

    protected function sanOptions(int $pid, array $patterns): array
    {
        $options = [];
        foreach($this->configOptionGroupIds($pid) as $gid)
        {
            foreach(Capsule::table('tblproductconfigoptions')->where('gid', $gid)->get() as $option)
            {
                foreach($patterns as $role => $regex)
                {
                    if(preg_match($regex, (string)$option->optionname))
                    {
                        $display   = strpos($option->optionname, '|') !== false ? trim(substr($option->optionname, strpos($option->optionname, '|') + 1)) : '';
                        $options[] = ['id' => (int)$option->id, 'gid' => (int)$gid, 'role' => $role, 'display' => $display, 'optionname' => $option->optionname];
                        break;
                    }
                }
            }
        }

        return $options;
    }

    protected function otherSslModules(): array
    {
        $own    = array_merge($this->orderModules(), ['TSSGGSModule']);
        $result = [];
        foreach(Capsule::table('tblsslorders')->selectRaw('module, COUNT(*) AS c')->whereNotIn('module', $own)->groupBy('module')->get() as $row)
        {
            $result[(string)$row->module] = (int)$row->c;
        }

        return $result;
    }

    protected function countBy(string $table, string $column, callable $where = null): array
    {
        if(!Schema::tableExists($table))
        {
            return [];
        }
        $query = Capsule::table($table)->selectRaw(Schema::quoteIdentifier($column) . ' AS k, COUNT(*) AS c')->groupBy($column);
        if($where)
        {
            $where($query);
        }
        $out = [];
        foreach($query->get() as $row)
        {
            $out[(string)$row->k] = (int)$row->c;
        }

        return $out;
    }

    // ---- Gateway-shaped builders ---- //

    /**
     * Order payload in the shape of POST /certificates/{category}
     */
    /** Gateway product id as the gateway sends it: int for numeric ids (GoGetSSL), string codes otherwise (TheSSLStore) */
    public static function productIdValue($id)
    {
        $id = trim((string)$id);

        return ctype_digit($id) ? (int)$id : $id;
    }

    protected function buildOrderPayload($remoteProductId, int $termMonths, string $csr, string $commonName, string $cnMethod, array $sans, array $contacts): array
    {
        $payload = [
            'product'     => ['id' => self::productIdValue($remoteProductId), 'term' => $termMonths > 0 ? $termMonths : 12],
            'csr'         => $csr,
            'common_name' => ['name' => $commonName, 'validation_method' => $cnMethod],
            'contacts'    => $contacts,
        ];
        if($sans)
        {
            $payload['alternative_names'] = array_values($sans);
        }

        return $payload;
    }

    /**
     * Contact block in the gateway shape from flat legacy fields (empty values are kept as '' so reissue forms prefill)
     */
    protected function buildContacts(array $admin, array $tech = null, array $organization = null): array
    {
        $contacts = [
            'administrator' => [
                'first_name' => (string)($admin['first_name'] ?? ''),
                'last_name'  => (string)($admin['last_name'] ?? ''),
                'title'      => (string)($admin['title'] ?? ''),
                'email'      => (string)($admin['email'] ?? ''),
                'phone'      => (string)($admin['phone'] ?? ''),
            ],
        ];
        $contacts['technical'] = $tech ? [
            'first_name' => (string)($tech['first_name'] ?? ''),
            'last_name'  => (string)($tech['last_name'] ?? ''),
            'title'      => (string)($tech['title'] ?? ''),
            'email'      => (string)($tech['email'] ?? ''),
            'phone'      => (string)($tech['phone'] ?? ''),
        ] : $contacts['administrator'];

        if($organization && !empty($organization['name']))
        {
            $contacts['organization'] = [
                'name'           => (string)$organization['name'],
                'division'       => (string)($organization['division'] ?? ''),
                'duns'           => (string)($organization['duns'] ?? ''),
                'address_line_1' => (string)($organization['address'] ?? ''),
                'address'        => (string)($organization['address'] ?? ''),
                'city'           => (string)($organization['city'] ?? ''),
                'region'         => (string)($organization['region'] ?? ''),
                'country'        => (string)($organization['country'] ?? ''),
                'postal_code'    => (string)($organization['postalcode'] ?? ''),
                'postalcode'     => (string)($organization['postalcode'] ?? ''),
                'phone'          => (string)($organization['phone'] ?? ''),
            ];
        }

        return $contacts;
    }

    /**
     * Cached order response in the shape of GET /certificates/{category}/{id} (+ 'orderData' = order payload)
     * and GET .../files. Only the paths the unified code reads are filled.
     */
    protected function buildCertificate(array $args): array
    {
        $status     = (string)$args['status'];
        $itemId     = (int)$args['item_id'];
        $orderId    = (int)($args['order_id'] ?? $itemId);
        $domains    = [];
        $cnApproved = in_array($status, ['active'], true);

        foreach($args['domains'] as $domain)
        {
            $domains[] = [
                'name'       => (string)$domain['name'],
                'method'     => (string)($domain['method'] ?? ''),
                'approved'   => isset($domain['approved']) ? (bool)$domain['approved'] : $cnApproved,
                'validation' => $domain['validation'] ?? [],
            ];
        }

        $files = [];
        if(!empty($args['crt']))
        {
            $files[] = ['name' => str_replace('.', '_', (string)$args['common_name']) . '.crt', 'content' => $args['crt']];
        }
        if(!empty($args['ca']))
        {
            $files[] = ['name' => 'CA.crt', 'content' => $args['ca']];
        }

        return [
            'orderData'  => [
                'order'     => [
                    'id'           => $orderId,
                    'status'       => $status,
                    'date'         => (string)($args['order_date'] ?? ''),
                    'total_amount' => (float)($args['api_price'] ?? 0),
                ],
                'items'     => [[
                    'id'           => $itemId,
                    'product_id'   => self::productIdValue($args['remote_product_id']),
                    'status'       => $status,
                    'vendor'       => ['id' => (string)($args['vendor_order_id'] ?? '')],
                    'csr'          => (string)$args['csr'],
                    'contacts'     => $args['order_payload']['contacts'] ?? [],
                    'domains'      => $domains,
                    'subscription' => [
                        'begin' => (string)($args['subscription_begin'] ?? $args['valid_from'] ?? ''),
                        'end'   => (string)($args['subscription_end'] ?? $args['valid_till'] ?? ''),
                    ],
                ]],
                'orderData' => $args['order_payload'],
                'migrated'  => ['source' => $this->source(), 'at' => date('Y-m-d H:i:s'), 'refreshed' => false],
            ],
            'orderFiles' => $files ? [
                'files'    => $files,
                'validity' => ['begin' => (string)($args['valid_from'] ?? ''), 'end' => (string)($args['valid_till'] ?? '')],
            ] : [],
        ];
    }

    /**
     * Legacy / vendor status → gateway order status
     */
    protected function normalizeStatus(string $status, string $sslOrderStatus): string
    {
        $s = strtolower(trim($status));
        $map = [
            'active' => 'active', 'issued' => 'active', 'completed' => 'active', 'valid' => 'active',
            'pending' => 'pending', 'processing' => 'processing', 'pending validation' => 'processing', 'awaiting ca' => 'processing',
            'pending_issuance_install' => 'processing', 'pending_validation' => 'processing', 'awaiting configuration' => 'pending',
            'cancelled' => 'cancelled', 'canceled' => 'cancelled', 'refunded' => 'cancelled', 'requested refund' => 'cancelled',
            'expired' => 'expired', 'revoked' => 'cancelled', 'rejected' => 'rejected', 'filed' => 'filed', 'incomplete' => 'processing',
        ];
        if(isset($map[$s]))
        {
            return $map[$s];
        }

        switch($sslOrderStatus)
        {
            case 'Completed':               return 'active';
            case self::STATUS_SUBMITTED:     return 'processing';
            case 'Expired':                 return 'expired';
            case 'Cancelled':
            case 'Requested Refund':        return 'cancelled';
        }

        return 'processing';
    }

    protected function targetSslStatus(string $legacyStatus, string $gatewayStatus): string
    {
        if($legacyStatus === self::STATUS_AWAITING)
        {
            return self::STATUS_AWAITING;
        }
        if(in_array($gatewayStatus, ['active', 'pending', 'processing'], true))
        {
            return self::STATUS_SUBMITTED;
        }

        // inactive: keep the legacy value so the history stays visible and crons ignore it
        return $legacyStatus ?: 'Cancelled';
    }

    protected function cnMethod(string $method): string
    {
        $m = strtolower(trim($method));
        if($m === '' || strpos($m, '@') !== false)
        {
            return $m; // approver e-mail address
        }
        $map = ['email' => 'email', 'http' => 'http', 'https' => 'https', 'dns' => 'dns', 'cname' => 'dns', 'dns_cname_token' => 'dns',
                'cname_csr_hash' => 'dns', 'http_csr_hash' => 'http', 'https_csr_hash' => 'https', 'file' => 'http', 'txt' => 'dns'];

        return $map[$m] ?? $m;
    }
}
