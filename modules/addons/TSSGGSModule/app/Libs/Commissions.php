<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use WHMCS\Database\Capsule;

/**
 * Per-client commissions: a % added to (or, negative, taken off) the product price for a client.
 * Rules: client + product, client + all products, all clients + product, all clients + all products. The most specific rule wins.
 */
class Commissions
{
    const TABLE = 'TSSGGSModule_UserCommissions';

    protected static $tableChecked = false;
    protected static $cache        = [];

    public static function createTable()
    {
        if(self::$tableChecked)
        {
            return;
        }
        if(!Capsule::schema()->hasTable(self::TABLE))
        {
            Capsule::schema()->create(self::TABLE, function($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('client_id')->default(0)->index();
                $table->integer('product_id')->default(0)->index();
                $table->decimal('commission', 8, 2);
                $table->dateTime('created_at');
                $table->unique(['client_id', 'product_id']);
            });
        }
        self::$tableChecked = true;
    }

    public static function all()
    {
        self::createTable();

        return Capsule::table(self::TABLE . ' as r')
            ->leftJoin('tblclients as c', 'c.id', '=', 'r.client_id')
            ->leftJoin('tblproducts as p', 'p.id', '=', 'r.product_id')
            ->orderBy('r.client_id')->orderBy('r.product_id')
            ->get(['r.*', 'c.firstname', 'c.lastname', 'c.companyname', 'p.name as product']);
    }

    public static function set($clientId, $productId, $commission)
    {
        self::createTable();
        if($productId && !Capsule::table('tblproducts')->where('id', (int)$productId)->where('servertype', 'TSSGGSModule')->exists())
        {
            throw new \InvalidArgumentException('Unknown product');
        }
        if($clientId && !Capsule::table('tblclients')->where('id', (int)$clientId)->exists())
        {
            throw new \InvalidArgumentException('Unknown client');
        }
        if(!is_numeric($commission) || (float)$commission <= -100)
        {
            throw new \InvalidArgumentException('The commission must be a number greater than -100');
        }

        Capsule::table(self::TABLE)->updateOrInsert(
            ['client_id' => (int)$clientId, 'product_id' => (int)$productId],
            ['commission' => round((float)$commission, 2), 'created_at' => date('Y-m-d H:i:s')]
        );
        self::$cache = [];
    }

    public static function delete($id)
    {
        self::createTable();
        self::$cache = [];

        return Capsule::table(self::TABLE)->where('id', (int)$id)->delete();
    }

    /**
     * Commission % for the client and product, null when no rule applies
     */
    public static function forProduct($clientId, $productId)
    {
        $key = (int)$clientId . ':' . (int)$productId;
        if(!array_key_exists($key, self::$cache))
        {
            self::createTable();
            $rules = Capsule::table(self::TABLE)
                ->whereIn('client_id', array_unique([0, (int)$clientId]))
                ->whereIn('product_id', [0, (int)$productId])
                ->get();

            $best = null;
            foreach($rules as $rule)
            {
                // client + product (3) > client + all products (2) > all clients + product (1) > all clients + all products (0)
                $rank = ((int)$rule->client_id ? 2 : 0) + ((int)$rule->product_id ? 1 : 0);
                if($best === null || $rank > $best[0]))
                {
                    $best = [$rank, (float)$rule->commission];
                }
            }
            self::$cache[$key] = $best ? $best[1] : null;
        }

        return self::$cache[$key];
    }

    public static function apply($amount, $commission)
    {
        return round((float)$amount * (1 + (float)$commission / 100), 2);
    }

    /**
     * Price of the product for the client in the client's currency and billing cycle (null = no rule / no price)
     */
    public static function price($clientId, $productId, $billingCycle, $currencyId)
    {
        $commission = self::forProduct($clientId, $productId);
        $product    = Capsule::table('tblproducts')->where('id', (int)$productId)->first(['paytype', 'servertype']);
        if($commission === null || !$product || $product->servertype !== 'TSSGGSModule' || $product->paytype === 'free')
        {
            return null;
        }

        $column = strtolower(str_replace(['-', ' '], '', (string)$billingCycle));
        if($product->paytype === 'onetime' || !in_array($column, ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'], true))
        {
            $column = 'monthly';
        }
        $base = Capsule::table('tblpricing')->where('type', 'product')->where('relid', (int)$productId)->where('currency', (int)$currencyId)->value($column);

        return $base === null || (float)$base < 0 ? null : self::apply($base, $commission);
    }
}
