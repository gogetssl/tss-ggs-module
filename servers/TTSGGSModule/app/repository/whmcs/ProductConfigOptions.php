<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs;

use ModulesGarden\Servers\TTSGGSModule\app\models\whmcs\ProductConfigOption;

class ProductConfigOptions {
    public static function get($pid, $serviceid, $billingCycle)
    {
        $results = [];
        $configuratbleOptions = getCartConfigOptions($pid, [], $billingCycle, $serviceid);
        foreach ($configuratbleOptions as $configuratbleOption)
        {
            if($configuratbleOption['selectedoption'] != 'SAN') continue;

            $configurable = ProductConfigOption::where('id', $configuratbleOption['id'])->first();
            if(isset($configurable->optionname) && !empty($configurable->optionname))
            {
                $optionname = explode('|', $configurable->optionname);
                $results[$optionname[0]] = $configuratbleOption['selectedqty'];
            }

        }
        return $results;
    }

}