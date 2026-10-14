<?php

use ModulesGarden\TSSGGSModule\App\Libs\AcmeHelpers;
use WHMCS\Database\Capsule;

$hookManager->register(
    function($params) {

        $pid = isset($_SESSION['cart']['products'][$params['i']]['pid']) ? (int)$_SESSION['cart']['products'][$params['i']]['pid'] : 0;

        if (!$pid || !AcmeHelpers::isAcmeWhmcsProduct($pid))
        {
            return;
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

        $configoptions = isset($params['configoption']) ? $params['configoption'] : [];
        $sansCount     = ($sansOptionId && isset($configoptions[$sansOptionId])) ? (int)$configoptions[$sansOptionId] : 0;
        $wildcardCount = ($wildcardOptionId && isset($configoptions[$wildcardOptionId])) ? (int)$configoptions[$wildcardOptionId] : 0;

        if ($sansCount <= 0 && $wildcardCount <= 0)
        {
            return [\ModulesGarden\TSSGGSModule\Core\translate('acme_order_requires_at_least_one_san')];
        }
    },
    1
);
