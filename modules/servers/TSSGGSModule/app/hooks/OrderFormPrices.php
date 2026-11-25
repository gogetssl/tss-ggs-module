<?php

namespace TSSGGSModule\Server\app\hooks;

use WHMCS\Database\Capsule;

/**
 * Prices shown on the standard order form (product list and configure product)
 *
 * WHMCS hook ClientAreaPageCart (registered in hooks.php).
 */
class OrderFormPrices
{
    public static function handle($vars)
    {
        try
        {
            list($clientId, $currencyId) = ClientCurrency::get();
            $scale = function($pricing, $pid) use ($clientId, $currencyId) {
                $markup = \TSSGGSModule\App\Libs\Billing\Markups::forProduct($clientId, $pid);
                if($markup === null || !is_array($pricing) || !Capsule::table('tblproducts')->where('id', (int)$pid)->where('servertype', 'TSSGGSModule')->exists())
                {
                    return $pricing;
                }
                foreach((array)($pricing['rawpricing'] ?? []) as $cycle => $amount)
                {
                    if(!is_numeric($amount) || (float)$amount <= 0)
                    {
                        continue;
                    }
                    $new = \TSSGGSModule\App\Libs\Billing\Markups::apply($amount, $markup);
                    $pricing['rawpricing'][$cycle] = number_format($new, 2, '.', '');
                    if(isset($pricing['cycles'][$cycle]))
                    {
                        $pricing['cycles'][$cycle] = str_replace((string)formatCurrency($amount, $currencyId), (string)formatCurrency($new, $currencyId), (string)$pricing['cycles'][$cycle]);
                    }
                }
                if(isset($pricing['minprice']['price']) && !empty($pricing['minprice']['cycle']) && isset($pricing['rawpricing'][$pricing['minprice']['cycle']]))
                {
                    $pricing['minprice']['price'] = formatCurrency($pricing['rawpricing'][$pricing['minprice']['cycle']], $currencyId);
                }

                return $pricing;
            };

            $return = [];
            if(!empty($vars['products']) && is_array($vars['products']))
            {
                $products = $vars['products'];
                foreach($products as $key => $product)
                {
                    if(isset($product['pid'], $product['pricing']))
                    {
                        $products[$key]['pricing'] = $scale($product['pricing'], (int)$product['pid']);
                    }
                }
                $return['products'] = $products;
            }
            if(!empty($vars['pricing']) && !empty($vars['productinfo']['pid']))
            {
                $return['pricing'] = $scale($vars['pricing'], (int)$vars['productinfo']['pid']);
            }

            return $return;
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }
}
