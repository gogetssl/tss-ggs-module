<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;


/**
 * ---- Client markups (Markups page) ---- //
 *
 * WHMCS hook OrderProductPricingOverride (registered in hooks.php).
 */
class CartPriceOverride
{
    public static function handle($vars)
    {
        try
        {
            list($clientId, $currencyId) = ClientCurrency::get();
            $price = \ModulesGarden\TSSGGSModule\App\Libs\Billing\Markups::price($clientId, (int)$vars['pid'], $vars['proddata']['billingcycle'] ?? 'annually', $currencyId);

            return $price === null ? [] : ['recurring' => $price];
        }
        catch(\Throwable $e)
        {
            return [];
        }
    }
}
