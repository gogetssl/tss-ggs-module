<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\Billing\FinancePolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * Catalogue changes (M-16): what changed in the provider's product list since the last sync, what the module did
 * about it (Settings > Finance > Catalogue sync mode) and the daily e-mail to the administrators.
 *
 * RemoteProduct::synchronize() keeps the previous copy of every product (RemoteProducts.rawData): before it is
 * overwritten, compare() records the differences in the CatalogueChanges table. apply() then retires / hides
 * discontinued products, shows returned ones again and imports new ones, as far as the mode allows, and notify()
 * e-mails what was recorded since the last e-mail. The Products page shows the list (tab "Catalogue changes").
 *
 * Modes (as in the TheSSLStore plugin 4.x):
 * - manual               nothing is changed, everything is reported
 * - alterations          changed products are fixed (SAN options, product settings); default
 * - alterations_removal  + discontinued products are retired (hidden only while they have active services)
 * - all                  + new products are imported, hidden, for the admin to review
 */
class CatalogueSync
{
    const MODES        = ['manual', 'alterations', 'alterations_removal', 'all'];
    const DEFAULT_MODE = 'alterations';

    const NEW          = 'new';
    const CHANGED      = 'changed';
    const DISCONTINUED = 'discontinued';
    const BACK         = 'back';
    const RETIRED      = 'retired';
    const HIDDEN       = 'hidden';
    const RESTORED     = 'restored';
    const IMPORTED     = 'imported';
    const WARNING      = 'warning';

    /** Products the sync hid or retired itself (shown again when the provider offers them again) */
    const AUTO_HIDDEN_SETTING = 'catalogueAutoHidden';

    /** A provider list shorter than this share of the products it had is taken as incomplete: nothing is marked missing */
    const MIN_LIST_SHARE = 0.5;

    const KEEP_DAYS = 365;
    const TAB_DAYS  = 90;

    /** Fields compared, with their name in the report */
    const FIELDS = [
        'name'         => 'Name',
        'validation'   => 'Validation',
        'category'     => 'Category',
        'san_included' => 'Included SANs (single / wildcard)',
        'san_limits'   => 'SAN limits (min - max)',
        'san_types'    => 'SAN types offered',
        'terms'        => 'Terms (months)',
    ];


    public static function table()
    {
        return Edition::table('CatalogueChanges');
    }

    public static function mode()
    {
        $mode = (string)GeneralSettings::get('catalogueSyncMode');

        return in_array($mode, self::MODES, true) ? $mode : self::DEFAULT_MODE;
    }

    /** Changed products are fixed automatically */
    public static function fixes()
    {
        return self::mode() !== 'manual';
    }

    /** Discontinued products are retired / hidden automatically */
    public static function removes()
    {
        return in_array(self::mode(), ['alterations_removal', 'all'], true);
    }

    /** New products are imported automatically (hidden) */
    public static function adds()
    {
        return self::mode() === 'all';
    }

    /* ---------------------------------------------------------------- recording (RemoteProduct::synchronize) */

    /**
     * The stored copies of a provider's products, before the sync overwrites them: remoteId => rawData
     */
    public static function before($vendor)
    {
        $before = [];
        foreach(Capsule::table(Edition::table('RemoteProducts'))->where('vendor', $vendor)->get(['remoteId', 'rawData']) as $row)
        {
            $before[(string)$row->remoteId] = json_decode((string)$row->rawData, true) ?: [];
        }

        return $before;
    }

    /**
     * A product the provider returned, compared with its stored copy (null = not stored before). Nothing is recorded
     * on the very first sync of a provider ($baseline): every product would be "new".
     */
    public static function compare($vendor, RemoteProduct $model, $old, array $new, $baseline)
    {
        if($baseline)
        {
            return;
        }
        try
        {
            $pid  = self::whmcsProductId($vendor, $model->remoteId);
            $name = (string)($new['label'] ?? $model->name);

            if($old === null)
            {
                if(ProductCatalog::importable($model))
                {
                    self::record($vendor, $model->remoteId, $name, 0, self::NEW, '', '', self::describe($new),
                        self::adds() ? 'To be imported (hidden) by the catalogue sync' : 'Not imported: import it on the Products page to sell it');
                }

                return;
            }

            if(!empty($old['missing_since']))
            {
                self::record($vendor, $model->remoteId, $name, $pid, self::BACK, '', 'missing since ' . $old['missing_since'], 'offered again',
                    $pid ? (self::removes() && in_array($pid, self::autoHidden(), true) ? 'To be shown again by the catalogue sync' : 'Check whether to offer it again') : 'Not imported: no action needed');
            }

            // changes matter for products sold in WHMCS only
            if(!$pid)
            {
                return;
            }
            $before = self::fields($old);
            $after  = self::fields($new);
            foreach($after as $field => $value)
            {
                $was = $before[$field] ?? '';
                if((string)$was === (string)$value)
                {
                    continue;
                }
                self::record($vendor, $model->remoteId, $name, $pid, self::CHANGED, $field, $was, $value, self::changeAction($field, $pid));
            }
        }
        catch(\Throwable $e)
        {
            // the sync itself must never fail because of the report
            \ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger::error('Catalogue changes: ' . $e->getMessage());
        }
    }

    /**
     * Whether a provider's list looks complete enough to mark the products it did not return as missing.
     * Records a warning (once a day) when it does not.
     */
    public static function listComplete($vendor, array $before, array $returnedIds)
    {
        $present = 0;
        foreach($before as $raw)
        {
            if(empty($raw['missing_since']))
            {
                $present++;
            }
        }
        if($present < 10 || count($returnedIds) >= $present * self::MIN_LIST_SHARE)
        {
            return true;
        }

        $today = Capsule::table(self::table())->where('vendor', $vendor)->where('type', self::WARNING)->where('created_at', '>=', date('Y-m-d 00:00:00'))->exists();
        if(!$today)
        {
            self::record($vendor, '', '', 0, self::WARNING, '', $present . ' products', count($returnedIds) . ' products',
                'The provider returned far fewer products than before: nothing was marked as discontinued or retired. If it persists, check the API connection.');
        }

        return false;
    }

    /** ProductCatalog::markMissing marked a product the provider no longer returns */
    public static function discontinued($vendor, $remoteId, array $raw)
    {
        try
        {
            $pid = self::whmcsProductId($vendor, $remoteId);
            self::record($vendor, $remoteId, (string)($raw['label'] ?? ''), $pid, self::DISCONTINUED, '', 'offered', 'no longer in the provider\'s list',
                !$pid ? 'Not imported: no action needed'
                    : (self::removes() ? 'To be retired by the catalogue sync if still missing tomorrow (only hidden while it has active services)' : 'Review it on the Products page: hide or retire it'));
        }
        catch(\Throwable $e)
        {
            \ModulesGarden\TSSGGSModule\Packages\Logs\Support\Facades\Logger::error('Catalogue changes: ' . $e->getMessage());
        }
    }

    /**
     * The values compared, as text
     */
    public static function fields(array $raw)
    {
        $san   = (array)($raw['san'] ?? []);
        $types = [];
        if(!empty($san['single_allowed']))
        {
            $types[] = 'single';
        }
        if(!empty($san['wildcard_allowed']))
        {
            $types[] = 'wildcard';
        }

        $fields = [
            'name'         => trim((string)($raw['label'] ?? '')),
            'validation'   => strtoupper(trim((string)($raw['validation'] ?? ''))),
            'category'     => strtolower(trim((string)($raw['category'] ?? ''))),
            'san_included' => (int)($san['included']['single'] ?? 0) . ' / ' . (int)($san['included']['wildcard'] ?? 0),
            'san_limits'   => (int)($san['min'] ?? 0) . ' - ' . (int)($san['max'] ?? 0),
            'san_types'    => $types ? implode(', ', $types) : 'none',
        ];

        $terms = [];
        foreach((array)($raw['prices'] ?? []) as $price)
        {
            $term = (int)($price['term'] ?? 0);
            if($term <= 0)
            {
                continue;
            }
            $terms[] = $term;
            $cost    = self::cost($price);
            if($cost !== null)
            {
                $fields['cost_' . $term] = number_format($cost, 2, '.', '');
            }
        }
        sort($terms);
        $fields['terms'] = implode(', ', array_unique($terms));

        return $fields;
    }

    /** The reseller cost of a term, as the import and the price update read it */
    public static function cost(array $price)
    {
        foreach([['base', 'single', 'selling'], ['base', 'wildcard', 'selling'], ['selling']] as $path)
        {
            $value = $price;
            foreach($path as $key)
            {
                $value = is_array($value) && array_key_exists($key, $value) ? $value[$key] : null;
            }
            if($value !== null && $value !== '')
            {
                return (float)$value;
            }
        }

        return null;
    }

    public static function fieldLabel($field)
    {
        if(strpos((string)$field, 'cost_') === 0)
        {
            return 'Reseller cost, ' . (int)substr($field, 5) . ' months (USD)';
        }

        return self::FIELDS[$field] ?? (string)$field;
    }

    protected static function describe(array $raw)
    {
        $fields = self::fields($raw);
        $parts  = [$fields['validation'] ?: '-', $fields['category'] ?: '-'];
        if($fields['terms'] !== '')
        {
            $parts[] = 'terms ' . $fields['terms'] . ' months';
        }
        $first = (int)explode(', ', $fields['terms'])[0];
        if($first && isset($fields['cost_' . $first]))
        {
            $parts[] = 'cost ' . $fields['cost_' . $first] . ' USD / ' . $first . ' months';
        }

        return implode(', ', $parts);
    }

    protected static function changeAction($field, $pid)
    {
        if(strpos((string)$field, 'cost_') === 0)
        {
            $auto = strtolower((string)Capsule::table('tblproducts')->where('id', (int)$pid)->value('configoption6')) === 'on';

            return $auto ? 'Selling prices follow automatically (automatic pricing)' : 'Manual prices: review them on the Pricing page';
        }
        if($field === 'name')
        {
            return 'For information: the WHMCS product keeps its name';
        }
        if(in_array($field, ['san_included', 'san_limits', 'san_types', 'validation', 'category'], true))
        {
            return self::fixes() ? 'Product settings and SAN options updated automatically' : 'Not changed (mode "Manually"): use Fix options on the Products page';
        }

        return 'For information';
    }

    protected static function record($vendor, $remoteId, $name, $pid, $type, $field, $old, $new, $action)
    {
        Capsule::table(self::table())->insert([
            'vendor'       => (string)$vendor,
            'remote_id'    => (string)$remoteId,
            'product_name' => mb_substr((string)$name, 0, 255),
            'whmcs_pid'    => (int)$pid,
            'type'         => $type,
            'field'        => (string)$field,
            'old_value'    => (string)$old,
            'new_value'    => (string)$new,
            'action'       => (string)$action,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    protected static function whmcsProductId($vendor, $remoteId)
    {
        return (int)Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->where('configoption1', $vendor)->where('configoption2', $remoteId)->value('id');
    }

    /* ---------------------------------------------------------------- acting (daily cron) */

    /**
     * Retires / hides discontinued products, shows returned ones again and imports new ones, as the mode allows
     * @return array counts by type
     */
    public static function apply()
    {
        $done = [self::RETIRED => 0, self::HIDDEN => 0, self::RESTORED => 0, self::IMPORTED => 0];
        if(self::removes())
        {
            $done = array_merge($done, self::removeDiscontinued(), [self::RESTORED => self::restoreReturned()]);
        }
        if(self::adds())
        {
            $done[self::IMPORTED] = self::importNew();
        }

        return $done;
    }

    /**
     * Imported products missing from the provider's list on an earlier day: retired, or only hidden while they have
     * services (existing certificates keep working either way)
     */
    protected static function removeDiscontinued()
    {
        $done   = [self::RETIRED => 0, self::HIDDEN => 0];
        $auto   = self::autoHidden();
        $today  = date('Y-m-d');
        foreach(Capsule::table(Edition::table('RemoteProducts'))->get(['vendor', 'remoteId', 'rawData']) as $row)
        {
            $raw = json_decode((string)$row->rawData, true) ?: [];
            if(empty($raw['missing_since']) || (string)$raw['missing_since'] >= $today)
            {
                continue; // present, or missing only since today: wait for the next day
            }
            $product = Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->where('configoption1', $row->vendor)->where('configoption2', $row->remoteId)->first(['id', 'name', 'hidden', 'retired']);
            if(!$product || (int)$product->retired)
            {
                continue;
            }
            $services = (int)Capsule::table('tblhosting')->where('packageid', $product->id)->whereIn('domainstatus', ['Active', 'Suspended', 'Pending'])->count();
            if($services > 0)
            {
                if((int)$product->hidden)
                {
                    continue;
                }
                ProductCatalog::setHidden($product->id, true);
                $type   = self::HIDDEN;
                $action = 'Hidden from the order form; not retired because ' . $services . ($services === 1 ? ' service uses' : ' services use') . ' it (it keeps working). Plan a replacement for the renewal' . ($services === 1 ? '' : 's');
            }
            else
            {
                ProductCatalog::setRetired($product->id, true);
                $type   = self::RETIRED;
                $action = 'Retired: it can no longer be ordered';
            }
            $auto[] = (int)$product->id;
            $done[$type]++;
            self::record($row->vendor, $row->remoteId, $product->name, $product->id, $type, '', 'missing since ' . $raw['missing_since'], $type, $action);
            GeneralSettings::activity('Catalogue sync: product #' . (int)$product->id . ' (' . $product->name . ') ' . $type . ', discontinued by the provider');
        }
        self::saveAutoHidden($auto);

        return $done;
    }

    /** Products this sync hid or retired that the provider offers again: shown again */
    protected static function restoreReturned()
    {
        $auto  = self::autoHidden();
        $keep  = [];
        $count = 0;
        foreach($auto as $pid)
        {
            $product = Capsule::table('tblproducts')->where('id', (int)$pid)->first(['id', 'name', 'configoption1', 'configoption2']);
            if(!$product)
            {
                continue;
            }
            $raw = json_decode((string)Capsule::table(Edition::table('RemoteProducts'))->where('vendor', $product->configoption1)->where('remoteId', $product->configoption2)->value('rawData'), true);
            if(!is_array($raw) || !empty($raw['missing_since']))
            {
                $keep[] = (int)$pid;
                continue;
            }
            ProductCatalog::setRetired($product->id, false);
            ProductCatalog::setHidden($product->id, false);
            $count++;
            self::record($product->configoption1, $product->configoption2, $product->name, $product->id, self::RESTORED, '', 'hidden / retired by the sync', 'offered again', 'Shown on the order form again');
            GeneralSettings::activity('Catalogue sync: product #' . (int)$product->id . ' (' . $product->name . ') shown again, offered by the provider again');
        }
        self::saveAutoHidden($keep);

        return $count;
    }

    /**
     * New products of the last 7 days not imported yet: imported with the default margin into a hidden group, the
     * product itself hidden, so nothing goes on sale before the admin checked it
     */
    protected static function importNew()
    {
        $rows = Capsule::table(self::table())->where('type', self::NEW)->where('whmcs_pid', 0)->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))->get();
        if(!count($rows))
        {
            return 0;
        }

        $count   = 0;
        $groupId = 0;
        foreach($rows as $row)
        {
            $remote = RemoteProduct::where('vendor', $row->vendor)->where('remoteId', $row->remote_id)->first();
            if(!$remote || !ProductCatalog::importable($remote) || !empty($remote->rawData['missing_since']))
            {
                continue;
            }
            $pid = (int)($remote->getWhmcsProduct()->id ?? 0);
            if(!$pid)
            {
                try
                {
                    $groupId = $groupId ?: self::reviewGroup();
                    $pid     = self::import($remote, $groupId);
                }
                catch(\Throwable $e)
                {
                    Capsule::table(self::table())->where('id', $row->id)->update(['action' => 'Import failed: ' . mb_substr($e->getMessage(), 0, 200)]);
                    continue;
                }
                if(!$pid)
                {
                    continue;
                }
                ProductCatalog::setHidden($pid, true);
                $count++;
                self::record($row->vendor, $row->remote_id, $row->product_name, $pid, self::IMPORTED, '', '', 'product #' . $pid,
                    'Imported hidden, with the default margin, into the hidden group "' . self::reviewGroupName() . '": check it, move it to a group and show it to sell it');
                GeneralSettings::activity('Catalogue sync: new product ' . $row->product_name . ' imported as product #' . $pid . ' (hidden)');
            }
            Capsule::table(self::table())->where('id', $row->id)->update(['whmcs_pid' => $pid, 'action' => 'Imported as product #' . $pid . ' (hidden)']);
        }

        return $count;
    }

    protected static function import(RemoteProduct $remote, $groupId)
    {
        $finance  = (array)((new \ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository())->getModuleConfiguration()['financeSettings'] ?? []);
        ProductImport::run([(int)$remote->id], (int)$groupId, '', (float)(FinancePolicy::refreshRate() ?: 1), (float)($finance['profitMargin'] ?? 0));

        return (int)($remote->getWhmcsProduct()->id ?? 0);
    }

    public static function reviewGroupName()
    {
        return Edition::name() . ': new products to review';
    }

    /** The hidden product group new products are imported into */
    protected static function reviewGroup()
    {
        $name = self::reviewGroupName();
        $id   = (int)Capsule::table('tblproductgroups')->where('name', $name)->value('id');
        if($id)
        {
            return $id;
        }
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
        $base = $slug;
        $i    = 1;
        while(Capsule::table('tblproductgroups')->where('slug', $slug)->exists())
        {
            $slug = $base . '-' . (++$i);
        }
        $columns = ['name' => $name, 'hidden' => 1, 'order' => (int)Capsule::table('tblproductgroups')->max('order') + 1];
        if(Capsule::schema()->hasColumn('tblproductgroups', 'slug'))
        {
            $columns['slug'] = $slug;
        }

        return (int)Capsule::table('tblproductgroups')->insertGetId($columns);
    }

    protected static function autoHidden()
    {
        $value = Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', self::AUTO_HIDDEN_SETTING)->value('value');

        return array_values(array_unique(array_map('intval', (array)json_decode((string)$value, true))));
    }

    protected static function saveAutoHidden(array $ids)
    {
        Capsule::table('tbladdonmodules')->updateOrInsert(['module' => 'configuration_TSSGGSModule', 'setting' => self::AUTO_HIDDEN_SETTING],
            ['value' => json_encode(array_values(array_unique(array_map('intval', $ids))))]);
    }

    /* ---------------------------------------------------------------- reporting */

    /**
     * E-mails the administrators what was recorded since the last e-mail (nothing recorded, no e-mail)
     * @return int number of changes e-mailed
     */
    public static function notify()
    {
        Capsule::table(self::table())->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . self::KEEP_DAYS . ' days')))->delete();

        $rows = Capsule::table(self::table())->whereNull('emailed_at')->orderBy('id')->get();
        if(!count($rows))
        {
            return 0;
        }
        $ids = [];
        foreach($rows as $row)
        {
            $ids[] = $row->id;
        }
        $counts = self::counts($rows);
        GeneralSettings::activity('Catalogue changes: ' . self::summary($counts));

        if(!GeneralSettings::enabled('catalogueChangesEmail'))
        {
            Capsule::table(self::table())->whereIn('id', $ids)->update(['emailed_at' => date('Y-m-d H:i:s')]);

            return 0;
        }

        $result = localAPI('SendAdminEmail', [
            'customsubject' => Edition::name() . ': catalogue changes - ' . self::summary($counts),
            'custommessage' => self::emailHtml($rows),
            'type'          => 'system',
        ]);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Catalogue changes e-mail to the administrators failed: ' . ($result['message'] ?? 'unknown error'));

            return 0; // tried again with the next run
        }
        Capsule::table(self::table())->whereIn('id', $ids)->update(['emailed_at' => date('Y-m-d H:i:s')]);

        return count($ids);
    }

    /** The order and the names of the change types, for the e-mail and the tab */
    public static function typeLabels()
    {
        return [
            self::WARNING      => 'Warning',
            self::NEW          => 'New',
            self::IMPORTED     => 'Imported',
            self::DISCONTINUED => 'Discontinued',
            self::RETIRED      => 'Retired',
            self::HIDDEN       => 'Hidden',
            self::BACK         => 'Offered again',
            self::RESTORED     => 'Shown again',
            self::CHANGED      => 'Changed',
        ];
    }

    protected static function counts($rows)
    {
        $counts = [];
        foreach($rows as $row)
        {
            $counts[$row->type] = ($counts[$row->type] ?? 0) + 1;
        }

        return $counts;
    }

    protected static function summary(array $counts)
    {
        $parts = [];
        foreach(self::typeLabels() as $type => $label)
        {
            if(!empty($counts[$type]))
            {
                $parts[] = $counts[$type] . ' ' . strtolower($label);
            }
        }

        return implode(', ', $parts);
    }

    protected static function emailHtml($rows)
    {
        $h       = function($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
        $byType  = [];
        foreach($rows as $row)
        {
            $byType[$row->type][] = $row;
        }
        $modes = ['manual' => 'Manually', 'alterations' => 'Only alterations', 'alterations_removal' => 'Alterations and removal', 'all' => 'All'];
        $html  = '<p>The product list of your certificate provider changed. Sync mode: <b>' . $h($modes[self::mode()]) . '</b> (Settings &gt; Finance).</p>';
        foreach(self::typeLabels() as $type => $label)
        {
            if(empty($byType[$type]))
            {
                continue;
            }
            $html .= '<h3>' . $h($label) . ' (' . count($byType[$type]) . ')</h3><table border="1" cellpadding="4" cellspacing="0">'
                . '<tr><th>Provider</th><th>Product</th><th>Change</th><th>Before</th><th>Now</th><th>What the module did / what to do</th></tr>';
            foreach(array_slice($byType[$type], 0, 150) as $row)
            {
                $product = $row->product_name . ((int)$row->whmcs_pid ? ' (#' . (int)$row->whmcs_pid . ')' : '');
                $html   .= '<tr><td>' . $h($row->vendor) . '</td><td>' . $h($product) . '</td><td>' . $h($row->field !== '' ? self::fieldLabel($row->field) : $label) . '</td><td>'
                    . $h($row->old_value) . '</td><td>' . $h($row->new_value) . '</td><td>' . $h($row->action) . '</td></tr>';
            }
            $html .= '</table>' . (count($byType[$type]) > 150 ? '<p>... and ' . (count($byType[$type]) - 150) . ' more.</p>' : '');
        }
        $html .= '<p>The full list is on the Products page of the module, tab "Catalogue changes".</p>';

        return $html;
    }

    /** Changes of the last days, newest first, for the Products page */
    public static function recent($days = self::TAB_DAYS, $limit = 1000)
    {
        return Capsule::table(self::table())->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-' . (int)$days . ' days')))
            ->orderBy('id', 'desc')->limit((int)$limit)->get();
    }
}
