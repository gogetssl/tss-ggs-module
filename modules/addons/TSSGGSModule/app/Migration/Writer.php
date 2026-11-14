<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\Markups;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\Libs\GatewayOrderService;
use WHMCS\Database\Capsule;

/**
 * Writes vendor-neutral records (see Readers\LegacyReaderInterface) into the unified module's tables.
 * Every method returns the ids it created so the engine can undo a single service or the whole run.
 */
class Writer
{
    const MODULE       = 'TSSGGSModule';
    public static function tRequests()
    {
        return Edition::table('Requests');
    }
    public static function tAcme()
    {
        return Edition::table('AcmeSubscriptions');
    }
    public static function tAcmeDomains()
    {
        return Edition::table('AcmeSubscriptionDomains');
    }
    public static function tAis()
    {
        return Edition::table('AisSubscriptions');
    }

    /** tblsslorders.updated_at value that puts migrated services first in the sync cron's "oldest first" sweep */
    const REFRESH_FIRST = '2000-01-01 00:00:00';

    protected $migrationId;
    protected $source;

    public function __construct(int $migrationId, string $source)
    {
        $this->migrationId = $migrationId;
        $this->source      = $source;
    }

    // ---- Settings ---- //

    /**
     * Merges legacy settings into the unified configuration: a legacy value is used only where the unified
     * setting is still empty (the admin's own entries from step 4 win). Returns the keys that were filled.
     */
    public function writeSettings(array $legacy): array
    {
        $repo    = new AddonModuleRepository();
        $current = $repo->getModuleConfiguration();
        $filled  = [];

        $ssl = (array)($current['sslSettings'] ?? []);
        foreach((array)($legacy['sslSettings'] ?? []) as $key => $value)
        {
            if((!isset($ssl[$key]) || $ssl[$key] === '' || $ssl[$key] === null) && $value !== '' && $value !== null)
            {
                $ssl[$key] = $value;
                $filled[]  = 'sslSettings.' . $key;
            }
        }
        $this->saveSetting('sslSettings', $ssl);

        $finance = (array)($current['financeSettings'] ?? []);
        foreach((array)($legacy['financeSettings'] ?? []) as $key => $value)
        {
            if((!isset($finance[$key]) || $finance[$key] === '' || $finance[$key] === null || (float)$finance[$key] == 0) && $value)
            {
                $finance[$key] = $value;
                $filled[]      = 'financeSettings.' . $key;
            }
        }
        if(!isset($finance['rate']) || !$finance['rate'])
        {
            $finance['rate'] = 1;
        }
        $this->saveSetting('financeSettings', $finance);

        // General settings: only while the unified module has none saved yet
        if(!empty($legacy['generalSettings']) && empty($current['generalSettings']))
        {
            $this->saveSetting('generalSettings', $legacy['generalSettings']);
            foreach(array_keys($legacy['generalSettings']) as $key)
            {
                $filled[] = 'generalSettings.' . $key;
            }
        }

        return $filled;
    }

    protected function saveSetting(string $setting, array $value): void
    {
        $row = Capsule::table('tbladdonmodules')->where('module', 'configuration_' . self::MODULE)->where('setting', $setting)->first();
        if($row)
        {
            Capsule::table('tbladdonmodules')->where('id', $row->id)->update(['value' => json_encode($value)]);
        }
        else
        {
            Capsule::table('tbladdonmodules')->insert(['module' => 'configuration_' . self::MODULE, 'setting' => $setting, 'value' => json_encode($value)]);
        }
    }

    // ---- Products ---- //

    /**
     * Rewrites configoption1..9 and renames the SAN options in place (ids, prices and bought quantities survive).
     * The servertype is NOT changed here (see switchProducts()).
     */
    public function writeProduct(array $product, RemoteProduct $remote): array
    {
        $options = ProductMapper::configOptionsFor($remote, (int)$product['included_san'], (int)$product['included_san_wildcard'], (bool)$product['price_auto']);
        $before  = (array)Capsule::table('tblproducts')->where('id', $product['pid'])->first();
        $old     = [];
        foreach(array_keys($options) as $col)
        {
            $old[$col] = $before[$col] ?? '';
        }
        // the unified module keeps nothing in the other configoptions
        for($i = 10; $i <= 24; $i++)
        {
            $old['configoption' . $i]     = $before['configoption' . $i] ?? '';
            $options['configoption' . $i] = '';
        }
        Capsule::table('tblproducts')->where('id', $product['pid'])->update($options);

        $renamed = [];
        foreach((array)$product['san_options'] as $option)
        {
            $display = $option['display'] !== '' ? $option['display'] : \ModulesGarden\TSSGGSModule\App\Libs\ProductCatalog::optionLabel($option['role'] === 'sans' ? 'single' : 'wildcard');
            $newName = $option['role'] . '|' . $display;
            Capsule::table('tblproductconfigoptions')->where('id', $option['id'])->update(['optionname' => $newName, 'hidden' => 0]);
            // the unified code counts sub-options named "SAN"
            Capsule::table('tblproductconfigoptionssub')->where('configid', $option['id'])->update(['optionname' => 'SAN']);
            $renamed[] = ['id' => $option['id'], 'from' => $option['optionname'], 'to' => $newName];

            Capsule::table('tblproductconfiggroups')->where('id', $option['gid'])->update([
                'name'        => 'Configurable options for ' . $product['name'] . ' product',
                'description' => 'Auto generated by module ' . Edition::name(),
            ]);
        }
        foreach((array)($product['other_options'] ?? []) as $option)
        {
            Capsule::table('tblproductconfigoptions')->where('id', $option['id'])->update(['hidden' => 1]);
        }
        // code signing: the legacy delivery option becomes the module's "Certificate Delivery" option, else a new one
        if(strtolower((string)$remote->category) === \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::CATEGORY)
        {
            $rawData = is_array($remote->rawData) ? $remote->rawData : [];
            if(!empty($product['cs_delivery_option']['id']))
            {
                \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::adoptLegacyDeliveryOption((int)$product['pid'], (int)$product['cs_delivery_option']['id'], $rawData);
            }
            else
            {
                \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::createDeliveryOption((int)$product['pid'], $rawData);
            }
        }

        // Products page settings (messages); the legacy product markup becomes an "all clients" markup
        if(!empty($product['product_settings']))
        {
            $this->saveSetting('product_' . (int)$product['pid'], array_merge(['months' => 0, 'margin' => '', 'processingGuide' => '', 'issuedMessage' => ''], $product['product_settings']));
        }
        $markupIds = [];
        if(!empty($product['markup_percent']))
        {
            $markupIds = $this->writeMarkups([['client_id' => 0, 'product_id' => (int)$product['pid'], 'percent' => $product['markup_percent']]]);
        }

        return ['pid' => (int)$product['pid'], 'old_configoptions' => $old, 'renamed_options' => $renamed, 'hidden_options' => array_column((array)($product['other_options'] ?? []), 'id'),
                'markup_ids' => $markupIds];
    }

    /**
     * Markup rules ([client_id, product_id, percent]); an existing rule of the unified module is kept.
     * Returns the ids it created (undone by Engine::rollback()).
     */
    public function writeMarkups(array $rules): array
    {
        Markups::createTable();
        $ids = [];
        foreach($rules as $rule)
        {
            $exists = Capsule::table(Markups::table())->where('client_id', (int)$rule['client_id'])->where('product_id', (int)$rule['product_id'])->exists();
            if($exists || ((int)$rule['client_id'] === 0 && (int)$rule['product_id'] === 0))
            {
                continue;
            }
            $ids[] = Capsule::table(Markups::table())->insertGetId([
                'client_id' => (int)$rule['client_id'], 'product_id' => (int)$rule['product_id'], 'markup' => (float)$rule['percent'], 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $ids;
    }

    /** JSON of legacy data: bytes that are not UTF-8 (old Latin-1 rows) are replaced, a value that can not be encoded stops the service */
    protected static function json($value): string
    {
        $json = json_encode($value, defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);
        if($json === false)
        {
            throw new \RuntimeException('Legacy data could not be encoded: ' . json_last_error_msg());
        }

        return $json;
    }

    public function switchProducts(array $pids): int
    {
        $count = 0;
        foreach(array_chunk($pids, 1000) as $chunk)
        {
            $count += (int)Capsule::table('tblproducts')->whereIn('id', $chunk)->update(['servertype' => self::MODULE]);
        }

        return $count;
    }

    // ---- Services ---- //

    /**
     * Writes one service inside a transaction. The tblsslorders.module value is NOT changed here (see switchOrders()).
     */
    public function writeService(array $rec, string $privateKeyPem = ''): array
    {
        // table creation inside the transaction would commit it implicitly (MySQL DDL)
        AutoInstallQueue::createTables();

        return Capsule::connection()->transaction(function() use ($rec, $privateKeyPem) {
            $now     = date('Y-m-d H:i:s');
            $written = ['ssl_order_id' => (int)$rec['ssl_order_id'], 'requests' => [], 'queue' => [], 'acme' => 0, 'acme_domains' => [], 'ais' => 0];

            $update = ['remoteid' => (string)($rec['remote_id'] ?? ''), 'status' => (string)$rec['target_status']];
            if(!empty($rec['configdata']))
            {
                $update['configdata'] = self::json($rec['configdata']);
            }
            Capsule::table('tblsslorders')->where('id', $rec['ssl_order_id'])->update($update);

            // remove any unified rows a previous attempt left behind
            Capsule::table(self::tRequests())->where('serviceid', $rec['service_id'])->whereIn('name', array_merge(['addOrder', 'certificate', 'autoinstall', 'renewOrder'], GatewayOrderService::requestNames()))->delete();

            $prices = (array)($rec['prices'] ?? []);
            $api    = (float)($prices['api_price'] ?? 0);
            $whmcs  = (float)($prices['whmcs_price'] ?? 0);

            if(!empty($rec['order_payload']))
            {
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'addOrder', $rec['order_payload'], (string)$rec['request_status'], $api, $whmcs, (int)($prices['invoiceid'] ?? 0));
            }
            if(!empty($rec['certificate']))
            {
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'certificate', $rec['certificate'], (string)$rec['request_status'], $api, $whmcs, (int)($prices['invoiceid'] ?? 0));
            }
            if(!empty($rec['codesigning']))
            {
                // code signing order (CodeSigningService::stored())
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'codesigning', $rec['codesigning'], (string)$rec['request_status'], $api, $whmcs, (int)($prices['invoiceid'] ?? 0));
            }
            if(!empty($rec['smime']))
            {
                // S/MIME order (SmimeService::stored())
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'smime', $rec['smime'], (string)$rec['request_status'], $api, $whmcs, (int)($prices['invoiceid'] ?? 0));
            }
            if(!empty($rec['codeguard']))
            {
                // CodeGuard licence (CodeGuardService::stored())
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'codeguard', $rec['codeguard'], (string)$rec['request_status'], $api, $whmcs, (int)($prices['invoiceid'] ?? 0));
            }
            if(!empty($rec['autoinstall']))
            {
                $state = $rec['autoinstall'];
                $state['private_key'] = $privateKeyPem !== '' ? \encrypt($privateKeyPem) : '';
                $state['migrated']    = ['source' => $this->source, 'migration_id' => $this->migrationId, 'legacy_status' => $state['legacy_status'] ?? ''];
                unset($state['legacy_status']);
                if($state['private_key'] === '')
                {
                    // no key: keep the record for history but do not let the queue act on it
                    $state['status']     = 'disabled';
                    $state['last_error'] = 'Private key not available after migration; a reissue will generate a new key';
                }
                $written['requests'][] = $this->insertRequest($rec['service_id'], 'autoinstall', $state, (string)$state['status'], 0, 0, 0);

                if($state['status'] !== 'disabled')
                {
                    foreach((array)($rec['queue'] ?? []) as $task)
                    {
                        $written['queue'][] = AutoInstallQueue::addTask($rec['client_id'], $rec['service_id'], $task['type'], $task['data']);
                    }
                }
                AutoInstallQueue::addLog($rec['client_id'], $rec['service_id'], 'info', 'Migrated from the ' . strtoupper($this->source) . ' module (migration #' . $this->migrationId . ')');
            }

            if(!empty($rec['subscription']['acme']))
            {
                $a = $rec['subscription']['acme'];
                Capsule::table(self::tAcme())->where('service_id', $rec['service_id'])->delete();
                Capsule::table(self::tAcmeDomains())->where('service_id', $rec['service_id'])->delete();
                $written['acme'] = (int)Capsule::table(self::tAcme())->insertGetId([
                    'service_id' => $rec['service_id'], 'client_id' => $a['client_id'], 'api_order_id' => $a['api_order_id'], 'acme_id' => $a['acme_id'],
                    'status' => $a['status'] ?: 'pending', 'acme_account_id' => $a['acme_account_id'], 'eab_kid' => $a['eab_kid'], 'eab_hmac_key' => $a['eab_hmac_key'],
                    'server_url' => $a['server_url'], 'period_start' => $a['period_start'], 'period_end' => $a['period_end'], 'renewal_date' => $a['renewal_date'],
                    'auto_renew' => $a['auto_renew'], 'cancelled_at' => $a['cancelled_at'], 'created_at' => $now, 'updated_at' => $now,
                ]);
                foreach((array)$a['domains'] as $d)
                {
                    $written['acme_domains'][] = (int)Capsule::table(self::tAcmeDomains())->insertGetId([
                        'service_id' => $rec['service_id'], 'domain' => $d['domain'], 'domain_type' => $d['domain_type'], 'status' => $d['status'],
                        'added_at' => $d['added_at'] ?: $now, 'removed_at' => $d['removed_at'], 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
            if(!empty($rec['subscription']['ais']))
            {
                $s = $rec['subscription']['ais'];
                Capsule::table(self::tAis())->where('service_id', $rec['service_id'])->delete();
                $written['ais'] = (int)Capsule::table(self::tAis())->insertGetId([
                    'service_id' => $rec['service_id'], 'client_id' => $s['client_id'], 'api_order_id' => $s['api_order_id'], 'ais_item_id' => $s['ais_item_id'],
                    'status' => $s['status'] ?: 'pending', 'login_sso_link' => $s['login_sso_link'], 'manage_sso_link' => $s['manage_sso_link'],
                    'autoinstall_status' => $s['autoinstall_status'], 'period_start' => $s['period_start'], 'period_end' => $s['period_end'],
                    'renewal_date' => $s['renewal_date'], 'auto_renew' => $s['auto_renew'], 'cancelled_at' => $s['cancelled_at'], 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            return $written;
        });
    }

    protected function insertRequest(int $serviceId, string $name, array $payload, string $status, float $apiPrice, float $whmcsPrice, int $invoiceId): int
    {
        return (int)Capsule::table(self::tRequests())->insertGetId([
            'invoiceid'  => $invoiceId ?: null,
            'serviceid'  => $serviceId,
            'name'       => $name,
            'api_price'  => $apiPrice,
            'rate'       => 1,
            'whmcs_price'=> $whmcsPrice,
            'diff_price' => $whmcsPrice - $apiPrice,
            'status'     => $status,
            'request'    => \encrypt(self::json($payload)),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Flips tblsslorders.module for the given order ids and marks them for a priority gateway refresh
     */
    public function switchOrders(array $sslOrderIds): int
    {
        if(!$sslOrderIds)
        {
            return 0;
        }
        $update = ['module' => self::MODULE];
        if(Capsule::schema()->hasColumn('tblsslorders', 'updated_at'))
        {
            $update['updated_at'] = self::REFRESH_FIRST;
        }

        $count = 0;
        foreach(array_chunk($sslOrderIds, 1000) as $chunk)
        {
            $count += (int)Capsule::table('tblsslorders')->whereIn('id', $chunk)->update($update);
        }

        return $count;
    }

    // ---- Undo ---- //

    /**
     * Removes the configurable options this run created for the given products (the code signing delivery option of a
     * product that had none): the option, its choices and their prices, and a group that was created with it.
     * Options that existed before the run are in the backup and are restored from it. Returns the removed option ids.
     */
    public function removeCreatedOptions(array $pids, Backup $backup): array
    {
        if(!$pids)
        {
            return [];
        }
        $prefix  = \ModulesGarden\TSSGGSModule\App\Libs\CodeSigningService::OPTION_PROVISIONING . '|';
        $options = Capsule::table('tblproductconfiglinks')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
            ->whereIn('tblproductconfiglinks.pid', $pids)
            ->where('tblproductconfigoptions.optionname', 'like', str_replace(['%', '_'], ['\\%', '\\_'], $prefix) . '%')
            ->get(['tblproductconfigoptions.id', 'tblproductconfigoptions.gid']);

        $removed = [];
        foreach($options as $option)
        {
            if($backup->hasRow('tblproductconfigoptions', (int)$option->id))
            {
                continue;
            }
            $subIds = array_map('intval', Capsule::table('tblproductconfigoptionssub')->where('configid', (int)$option->id)->pluck('id')->all());
            if($subIds)
            {
                Capsule::table('tblpricing')->where('type', 'configoptions')->whereIn('relid', $subIds)->delete();
                Capsule::table('tblproductconfigoptionssub')->whereIn('id', $subIds)->delete();
            }
            Capsule::table('tblproductconfigoptions')->where('id', (int)$option->id)->delete();
            $removed[] = (int)$option->id;

            // a group that was created for this option only
            if(!$backup->hasRow('tblproductconfiggroups', (int)$option->gid) && !Capsule::table('tblproductconfigoptions')->where('gid', (int)$option->gid)->exists())
            {
                Capsule::table('tblproductconfiglinks')->where('gid', (int)$option->gid)->delete();
                Capsule::table('tblproductconfiggroups')->where('id', (int)$option->gid)->delete();
            }
        }

        return $removed;
    }

    /**
     * Removes the unified rows a service write created (tblsslorders itself is restored from the backup)
     */
    public function undoService(array $written): void
    {
        if(!empty($written['requests']))
        {
            Capsule::table(self::tRequests())->whereIn('id', $written['requests'])->delete();
        }
        if(!empty($written['queue']))
        {
            Capsule::table(AutoInstallQueue::queueTable())->whereIn('id', $written['queue'])->delete();
        }
        if(!empty($written['acme']))
        {
            Capsule::table(self::tAcme())->where('id', $written['acme'])->delete();
        }
        if(!empty($written['acme_domains']))
        {
            Capsule::table(self::tAcmeDomains())->whereIn('id', $written['acme_domains'])->delete();
        }
        if(!empty($written['ais']))
        {
            Capsule::table(self::tAis())->where('id', $written['ais'])->delete();
        }
    }
}
