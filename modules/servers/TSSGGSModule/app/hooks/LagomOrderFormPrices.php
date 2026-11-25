<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\hooks;

use WHMCS\Database\Capsule;

/**
 * Lagom order form loads prices over AJAX - adjust them in the browser (the cart total comes from the override hook)
 *
 * WHMCS hook ClientAreaHeadOutput (registered in hooks.php).
 */
class LagomOrderFormPrices
{
    public static function handle($vars)
    {
        if(strpos((string)($vars['template'] ?? ''), 'lagom') === false)
        {
            return '';
        }
        try
        {
            list($clientId) = ClientCurrency::get();
            $markups = [];
            foreach(Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->pluck('id') as $pid)
            {
                $markup = \ModulesGarden\TSSGGSModule\App\Libs\Billing\Markups::forProduct($clientId, $pid);
                if($markup !== null)
                {
                    $markups[(int)$pid] = 1 + $markup / 100;
                }
            }
            if(!$markups)
            {
                return '';
            }
        }
        catch(\Throwable $e)
        {
            return '';
        }

        return '<script>(function () {
        var factors = ' . json_encode($markups) . ', months = {monthly: 1, quarterly: 3, semiannually: 6, annually: 12, biennially: 24, triennially: 36};
        function scale(pricing, factor, prefix) {
            Object.keys(pricing || {}).forEach(function (cycle) {
                var p = pricing[cycle], raw = parseFloat(p && p.rawPrice);
                if (isNaN(raw) || raw <= 0) { return; }
                var value = (raw * factor).toFixed(2);
                p.rawPrice = value;
                p.price = p.finalPrice = p.startingPrice = prefix + value;
                p.monthlyPrice = prefix + (value / (months[cycle] || 1)).toFixed(2);
            });
        }
        var open = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function (method, url) {
            if (url && String(url).indexOf("LagomOrderForm") !== -1) {
                var xhr = this;
                xhr.addEventListener("readystatechange", function () {
                    if (xhr.readyState !== 4) { return; }
                    try {
                        var data = JSON.parse(xhr.responseText), changed = false;
                        var prefix = data.settings && data.settings.currency ? data.settings.currency.prefix : "";
                        ((data.appState && data.appState.groups) || []).forEach(function (group) {
                            (group.products || []).forEach(function (product) {
                                var factor = factors[product.id];
                                if (!factor) { return; }
                                changed = true;
                                scale(product.pricing, factor, prefix);
                                Object.keys(product.pricings || {}).forEach(function (id) { scale(product.pricings[id], factor, prefix); });
                            });
                        });
                        if (changed) {
                            var text = JSON.stringify(data);
                            Object.defineProperty(xhr, "responseText", {get: function () { return text; }});
                            Object.defineProperty(xhr, "response", {get: function () { return text; }});
                        }
                    } catch (e) {}
                });
            }
            return open.apply(this, arguments);
        };
    })();</script>';
    }
}
