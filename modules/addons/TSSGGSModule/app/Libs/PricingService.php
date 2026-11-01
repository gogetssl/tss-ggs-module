<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use WHMCS\Database\Capsule;

/**
 * Prices of the module products against the reseller cost from the gateway catalogue:
 * pricing grid, "apply margin", below-cost detection and CSV export / import.
 * Items are the product itself ('base') and its SAN options ('sans', 'sans_wildcard').
 */
class PricingService
{
    const TERMS  = [12 => 'annually', 24 => 'biennially', 36 => 'triennially'];
    const ITEMS  = ['base', 'sans', 'sans_wildcard'];
    const REMOTE = ['sans' => 'single', 'sans_wildcard' => 'wildcard'];

    protected $rate;
    protected $baseCurrencyId;

    public function __construct()
    {
        $config               = (new AddonModuleRepository())->getModuleConfiguration();
        $this->rate           = (float)($config['financeSettings']['rate'] ?? 1) ?: 1;
        $this->baseCurrencyId = (int)Capsule::table('tblcurrencies')->where('code', 'USD')->value('id')
            ?: (int)Capsule::table('tblcurrencies')->where('default', 1)->value('id');
    }

    public static function currencies()
    {
        return Capsule::table('tblcurrencies')->orderBy('default', 'desc')->orderBy('code')->get(['id', 'code', 'prefix', 'suffix', 'default']);
    }

    /**
     * Module products with their gateway product (rawData decoded)
     */
    public function products()
    {
        $products = Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->orderBy('name')
            ->get(['id', 'name', 'paytype', 'hidden', 'retired', 'configoption1', 'configoption2', 'configoption6']);

        $result = [];
        foreach($products as $product)
        {
            $remote = Capsule::table('TSSGGSModule_RemoteProducts')->where('vendor', $product->configoption1)->where('remoteId', $product->configoption2)->first();
            $result[(int)$product->id] = [
                'id'      => (int)$product->id,
                'name'    => (string)$product->name,
                'vendor'  => (string)$product->configoption1,
                'paytype' => (string)$product->paytype,
                'auto'    => strtolower((string)$product->configoption6) === 'on',
                'remote'  => $remote ? (json_decode($remote->rawData, true) ?: []) : null,
                'options' => $this->sanOptions((int)$product->id),
            ];
        }

        return $result;
    }

    /**
     * ['sans' => sub-option id, 'sans_wildcard' => sub-option id] of the product's SAN options
     */
    public function sanOptions($productId)
    {
        $rows = Capsule::table('tblproductconfiglinks')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiglinks.gid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.configid', '=', 'tblproductconfigoptions.id')
            ->where('tblproductconfiglinks.pid', (int)$productId)
            ->get(['tblproductconfigoptions.optionname', 'tblproductconfigoptionssub.id as subId']);

        $options = [];
        foreach($rows as $row)
        {
            $name = explode('|', (string)$row->optionname)[0];
            if(isset(self::REMOTE[$name]) && !isset($options[$name]))
            {
                $options[$name] = (int)$row->subId;
            }
        }

        return $options;
    }

    /**
     * Reseller cost of an item for a term in the gateway currency, null when the gateway has no price
     */
    public function remoteCost(?array $remote, $item, $term)
    {
        foreach((array)($remote['prices'] ?? []) as $price)
        {
            if((int)($price['term'] ?? 0) !== (int)$term)
            {
                continue;
            }
            if($item === 'base')
            {
                $value = $price['base']['single']['selling'] ?? ($price['base']['wildcard']['selling'] ?? ($price['selling'] ?? null));
            }
            else
            {
                $value = $price['san'][self::REMOTE[$item]]['selling'] ?? null;
            }

            return $value === null ? null : (float)$value;
        }

        return null;
    }

    /**
     * Gateway amount in a WHMCS currency (same conversion as the automatic price update)
     */
    public function convert($amount, $currencyId)
    {
        $base = (float)$amount * $this->rate;

        return (int)$currencyId === $this->baseCurrencyId ? round($base, 2) : round((float)\convertCurrency($base, $this->baseCurrencyId, (int)$currencyId), 2);
    }

    /**
     * Current WHMCS price, null when the cycle is disabled
     */
    public function price(array $product, $item, $term, $currencyId)
    {
        $type  = $item === 'base' ? 'product' : 'configoptions';
        $relid = $item === 'base' ? $product['id'] : ($product['options'][$item] ?? 0);
        if(!$relid)
        {
            return null;
        }

        $column = $product['paytype'] === 'onetime' ? 'monthly' : self::TERMS[$term];
        if($product['paytype'] === 'onetime' && $term !== 12)
        {
            return null;
        }
        $value = Capsule::table('tblpricing')->where('type', $type)->where('relid', $relid)->where('currency', (int)$currencyId)->value($column);

        return $value === null || (float)$value < 0 ? null : (float)$value;
    }

    public function setPrice(array $product, $item, $term, $currencyId, $value)
    {
        $type  = $item === 'base' ? 'product' : 'configoptions';
        $relid = $item === 'base' ? $product['id'] : ($product['options'][$item] ?? 0);
        if(!$relid)
        {
            return false;
        }

        $columns = [self::TERMS[$term] => $value];
        if($product['paytype'] === 'onetime' && $term === 12)
        {
            $columns['monthly'] = $value;
        }
        Capsule::table('tblpricing')->updateOrInsert(['type' => $type, 'relid' => $relid, 'currency' => (int)$currencyId], $columns);

        return true;
    }

    /**
     * Grid rows for one currency: product + SAN options x terms with price, cost, margin % and below-cost flag
     */
    public function grid($currencyId)
    {
        $rows = [];
        foreach($this->products() as $product)
        {
            foreach(self::ITEMS as $item)
            {
                if($item !== 'base' && empty($product['options'][$item]))
                {
                    continue;
                }
                $cells = [];
                foreach(array_keys(self::TERMS) as $term)
                {
                    $remoteCost = $this->remoteCost($product['remote'], $item, $term);
                    $cost       = $remoteCost === null ? null : $this->convert($remoteCost, $currencyId);
                    $price      = $this->price($product, $item, $term, $currencyId);
                    $cells[$term] = [
                        'price'      => $price,
                        'cost'       => $cost,
                        'margin'     => ($price !== null && $cost) ? round(($price - $cost) / $cost * 100, 1) : null,
                        'below_cost' => $price !== null && $cost !== null && $price > 0 && $price < $cost,
                        'available'  => $cost !== null && !($product['paytype'] === 'onetime' && $term !== 12),
                    ];
                }
                $rows[] = ['product' => $product, 'item' => $item, 'cells' => $cells];
            }
        }

        return $rows;
    }

    /**
     * Sets price = cost + margin % for every priced cycle (all currencies when $currencyId is null)
     */
    public function applyMargin($margin, $currencyId = null, array $productIds = [])
    {
        $changed    = 0;
        $currencies = $currencyId ? [(int)$currencyId] : self::currencies()->pluck('id')->all();
        foreach($this->products() as $product)
        {
            if($productIds && !in_array($product['id'], $productIds, true))
            {
                continue;
            }
            foreach(self::ITEMS as $item)
            {
                foreach(array_keys(self::TERMS) as $term)
                {
                    $remoteCost = $this->remoteCost($product['remote'], $item, $term);
                    if($remoteCost === null || ($product['paytype'] === 'onetime' && $term !== 12))
                    {
                        continue;
                    }
                    foreach($currencies as $currency)
                    {
                        if($item === 'base' && $this->price($product, $item, $term, $currency) === null && $product['paytype'] !== 'onetime')
                        {
                            continue; // a disabled cycle stays disabled
                        }
                        $value = round($this->convert($remoteCost, $currency) * (1 + (float)$margin / 100), 2);
                        $changed += $this->setPrice($product, $item, $term, $currency, $value) ? 1 : 0;
                    }
                }
            }
        }

        return $changed;
    }

    /**
     * All prices below the reseller cost: [['product' => name, 'item' => .., 'term' => .., 'currency' => code, 'price' => .., 'cost' => ..]]
     */
    public function belowCost()
    {
        $list = [];
        foreach(self::currencies() as $currency)
        {
            foreach($this->grid($currency->id) as $row)
            {
                foreach($row['cells'] as $term => $cell)
                {
                    if($cell['below_cost'])
                    {
                        $list[] = ['product' => $row['product']['name'], 'pid' => $row['product']['id'], 'item' => $row['item'], 'term' => $term,
                                   'currency' => $currency->code, 'price' => $cell['price'], 'cost' => $cell['cost']];
                    }
                }
            }
        }

        return $list;
    }

    // ---- Sync: keep margin, below-cost notification ---- //

    const SNAPSHOT_SETTING = 'pricingCostSnapshot';
    const NOTIFIED_SETTING = 'pricingBelowCostNotified';

    /**
     * Products with manual prices ("Auto" off): when the reseller cost changed since the last run, the prices move
     * with it so the margin stays the same. The current costs are remembered for the next run.
     */
    public function keepMargins($apply = true)
    {
        $snapshot = json_decode((string)$this->setting(self::SNAPSHOT_SETTING), true) ?: [];
        $current  = [];
        $changed  = 0;
        $currencies = self::currencies()->pluck('id')->all();

        foreach($this->products() as $product)
        {
            foreach(self::ITEMS as $item)
            {
                foreach(array_keys(self::TERMS) as $term)
                {
                    $cost = $this->remoteCost($product['remote'], $item, $term);
                    if($cost === null)
                    {
                        continue;
                    }
                    $current[$product['id']][$item][$term] = $cost;
                    $old = $snapshot[$product['id']][$item][$term] ?? null;
                    if(!$apply || $product['auto'] || $old === null || (float)$old <= 0 || abs($old - $cost) < 0.005)
                    {
                        continue;
                    }
                    foreach($currencies as $currencyId)
                    {
                        $price = $this->price($product, $item, $term, $currencyId);
                        if($price !== null && $price > 0)
                        {
                            $changed += $this->setPrice($product, $item, $term, $currencyId, round($price * $cost / $old, 2)) ? 1 : 0;
                        }
                    }
                }
            }
        }

        $this->saveSetting(self::SNAPSHOT_SETTING, json_encode($current));

        return $changed;
    }

    /**
     * E-mails the administrators the prices below the reseller cost - once per distinct list
     */
    public function notifyBelowCost()
    {
        $list = $this->belowCost();
        $hash = $list ? sha1(json_encode($list)) : '';
        if($hash === (string)$this->setting(self::NOTIFIED_SETTING))
        {
            return 0;
        }
        $this->saveSetting(self::NOTIFIED_SETTING, $hash);
        if(!$list)
        {
            return 0;
        }

        $rows = '';
        foreach(array_slice($list, 0, 200) as $row)
        {
            $rows .= '<tr><td>' . htmlspecialchars($row['product']) . ' (#' . (int)$row['pid'] . ')</td><td>' . htmlspecialchars($row['item']) . '</td><td>' . (int)$row['term']
                . ' months</td><td>' . htmlspecialchars($row['currency']) . '</td><td>' . number_format($row['price'], 2) . '</td><td>' . number_format($row['cost'], 2) . '</td></tr>';
        }
        $message = '<p>' . count($list) . ' prices of the ' . htmlspecialchars(Edition::name()) . ' are below your reseller cost:</p>'
            . '<table border="1" cellpadding="4" cellspacing="0"><tr><th>Product</th><th>Item</th><th>Term</th><th>Currency</th><th>Price</th><th>Reseller cost</th></tr>' . $rows . '</table>'
            . (count($list) > 200 ? '<p>... and ' . (count($list) - 200) . ' more.</p>' : '')
            . '<p>Review them on the Pricing page of the module.</p>';

        $result = localAPI('SendAdminEmail', ['customsubject' => Edition::name() . ': ' . count($list) . ' prices below reseller cost', 'custommessage' => $message, 'type' => 'system']);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('Below-cost e-mail to the administrators failed: ' . ($result['message'] ?? 'unknown error'));
        }

        return count($list);
    }

    protected function setting($name)
    {
        return Capsule::table('tbladdonmodules')->where('module', 'configuration_TSSGGSModule')->where('setting', $name)->value('value');
    }

    protected function saveSetting($name, $value)
    {
        Capsule::table('tbladdonmodules')->updateOrInsert(['module' => 'configuration_TSSGGSModule', 'setting' => $name], ['value' => $value]);
    }

    // ---- CSV ---- //

    const CSV_HEADER = ['product_id', 'product', 'item', 'term_months', 'currency', 'reseller_cost', 'price'];

    public function exportCsv()
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, self::CSV_HEADER);
        foreach(self::currencies() as $currency)
        {
            foreach($this->grid($currency->id) as $row)
            {
                foreach($row['cells'] as $term => $cell)
                {
                    if(!$cell['available'] && $cell['price'] === null)
                    {
                        continue;
                    }
                    fputcsv($out, [$row['product']['id'], $row['product']['name'], $row['item'], $term, $currency->code,
                                   $cell['cost'] === null ? '' : number_format($cell['cost'], 2, '.', ''),
                                   $cell['price'] === null ? 'disabled' : number_format($cell['price'], 2, '.', '')]);
                }
            }
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }

    /**
     * Parses an uploaded CSV: ['accepted' => [...changes], 'rejected' => [...rows with reason], 'unchanged' => n]
     */
    public function previewCsv($csv)
    {
        $lines      = array_values(array_filter(preg_split('/\r\n|\r|\n/', (string)$csv), 'strlen'));
        $header     = array_map('trim', str_getcsv(array_shift($lines) ?: ''));
        $products   = $this->products();
        $currencies = [];
        foreach(self::currencies() as $currency)
        {
            $currencies[strtoupper($currency->code)] = (int)$currency->id;
        }

        $result = ['accepted' => [], 'rejected' => [], 'unchanged' => 0];
        if(array_diff(['product_id', 'item', 'term_months', 'currency', 'price'], $header))
        {
            $result['rejected'][] = ['line' => 1, 'data' => implode(',', $header), 'reason' => 'Missing columns - expected: ' . implode(', ', self::CSV_HEADER)];

            return $result;
        }

        foreach($lines as $index => $line)
        {
            $values = str_getcsv($line);
            $row    = count($values) === count($header) ? array_combine($header, array_map('trim', $values)) : null;
            $reject = function($reason) use (&$result, $index, $line) {
                $result['rejected'][] = ['line' => $index + 2, 'data' => $line, 'reason' => $reason];
            };

            if(!$row)
            {
                $reject('Wrong number of columns');
                continue;
            }
            $pid      = (int)$row['product_id'];
            $term     = (int)$row['term_months'];
            $currency = strtoupper($row['currency']);
            if(!isset($products[$pid]))
            {
                $reject('Unknown product ID ' . $row['product_id']);
                continue;
            }
            if(!in_array($row['item'], self::ITEMS, true) || ($row['item'] !== 'base' && empty($products[$pid]['options'][$row['item']])))
            {
                $reject('Unknown item "' . $row['item'] . '" for this product');
                continue;
            }
            if(!isset(self::TERMS[$term]) || ($products[$pid]['paytype'] === 'onetime' && $term !== 12))
            {
                $reject('Term ' . $row['term_months'] . ' is not available');
                continue;
            }
            if(!isset($currencies[$currency]))
            {
                $reject('Unknown currency ' . $row['currency']);
                continue;
            }
            $disable = strtolower($row['price']) === 'disabled' || $row['price'] === '-1';
            if(!$disable && (!is_numeric($row['price']) || (float)$row['price'] < 0))
            {
                $reject('Price "' . $row['price'] . '" is not a valid amount');
                continue;
            }
            $new = $disable ? null : round((float)$row['price'], 2);
            $old = $this->price($products[$pid], $row['item'], $term, $currencies[$currency]);
            if($disable && $row['item'] !== 'base')
            {
                if($old === null)
                {
                    $result['unchanged']++; // exported as "disabled" because it has no price yet
                }
                else
                {
                    $reject('SAN options can not be disabled per cycle - set the price to 0');
                }
                continue;
            }

            if($new === $old || ($new !== null && $old !== null && abs($new - $old) < 0.005))
            {
                $result['unchanged']++;
                continue;
            }
            $remoteCost = $this->remoteCost($products[$pid]['remote'], $row['item'], $term);
            $cost       = $remoteCost === null ? null : $this->convert($remoteCost, $currencies[$currency]);

            $result['accepted'][] = [
                'pid' => $pid, 'product' => $products[$pid]['name'], 'item' => $row['item'], 'term' => $term, 'currency' => $currency,
                'currency_id' => $currencies[$currency], 'old' => $old, 'new' => $new, 'cost' => $cost,
                'below_cost' => $new !== null && $cost !== null && $new > 0 && $new < $cost,
            ];
        }

        return $result;
    }

    /**
     * Applies the accepted rows of previewCsv()
     */
    public function applyCsv(array $accepted)
    {
        $products = $this->products();
        $count    = 0;
        foreach($accepted as $row)
        {
            if(isset($products[$row['pid']]))
            {
                $count += $this->setPrice($products[$row['pid']], $row['item'], (int)$row['term'], (int)$row['currency_id'], $row['new'] === null ? -1 : $row['new']) ? 1 : 0;
            }
        }

        return $count;
    }
}
