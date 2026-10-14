<?php

use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use WHMCS\Database\Capsule;

$hookManager->register(
    function($params) {
        $errors   = [];
        $products = isset($_SESSION['cart']['products']) ? $_SESSION['cart']['products'] : [];

        foreach ($products as $product)
        {
            $pid = isset($product['pid']) ? (int)$product['pid'] : 0;

            if (!$pid || !AcmeHelpers::isAcmeWhmcsProduct($pid))
            {
                continue;
            }

            $sansOptionId = Capsule::table('tblproductconfigoptions')
                ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
                ->where('tblproductconfiglinks.pid', $pid)
                ->where(function($q) {
                    $q->where('tblproductconfigoptions.optionname', 'sans')
                      ->orWhere('tblproductconfigoptions.optionname', 'LIKE', 'sans|%');
                })
                ->value('tblproductconfigoptions.id');

            $wildcardOptionId = Capsule::table('tblproductconfigoptions')
                ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
                ->where('tblproductconfiglinks.pid', $pid)
                ->where(function($q) {
                    $q->where('tblproductconfigoptions.optionname', 'sans_wildcard')
                      ->orWhere('tblproductconfigoptions.optionname', 'LIKE', 'sans_wildcard|%');
                })
                ->value('tblproductconfigoptions.id');

            $configoptions = isset($product['configoptions']) ? $product['configoptions'] : [];
            $sansCount     = ($sansOptionId    && isset($configoptions[$sansOptionId]))    ? (int)$configoptions[$sansOptionId]    : 0;
            $wildcardCount = ($wildcardOptionId && isset($configoptions[$wildcardOptionId])) ? (int)$configoptions[$wildcardOptionId] : 0;

            if ($sansCount <= 0 && $wildcardCount <= 0)
            {
                $errors[] = \ModulesGarden\TSSGGSModule\Core\translate('acme_order_requires_at_least_one_san');
            }
        }

        return $errors;
    },
    1
);
