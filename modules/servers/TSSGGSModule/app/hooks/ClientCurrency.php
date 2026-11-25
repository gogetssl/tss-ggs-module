<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;

use WHMCS\Database\Capsule;

/**
 * The visitor of the client area for the client markups (Markups page): [client id (0 = not logged in), currency id].
 * Used by the price hooks (CartPriceOverride, OrderFormPrices, LagomOrderFormPrices).
 */
class ClientCurrency
{
    public static function get(): array
    {
        $clientId = !empty($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
        if($clientId && function_exists('getCurrency'))
        {
            $currency = \getCurrency($clientId);

            return [$clientId, (int)$currency['id']];
        }
        $currencyId = !empty($_SESSION['currency']) ? (int)$_SESSION['currency'] : (int)Capsule::table('tblcurrencies')->where('default', 1)->value('id');

        return [0, $currencyId];
    }
}
