<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\repository\whmcs;

use WHMCS\Database\Capsule as DataBase;

class Config {

    public function getConfigureSSLUrl($id, $serviceID = null) {
        $WHMCSUrl = $this->getBrandURL($serviceID);
        return $WHMCSUrl . '/configuressl.php?cert=' . md5($id);
    }

    public function getBrandURL($id)
    {
        $mb1x = DataBase::table('tbladdonmodules')->where('module', 'MultibrandFunctionality')->first();
        $mb2x = DataBase::table('tbladdonmodules')->where('module', 'Multibrand')->first();

        $configuration = DataBase::table('tblconfiguration')->where('setting', 'SystemURL')->first();
        $systemURL = $configuration->value;

        if (function_exists('MultibrandFunctionalityAutoLoader') && !empty($mb1x)) {

            $brandData = [];
            MultibrandFunctionalityAutoLoader();
            $check = \MultibrandFunctionality\app\models\BrandRelations::factory()->searchFor('service', $id);
            if (isset($check->brand_id)) {
                $currentBrand = \MultibrandFunctionality\app\models\Brand::factory($check->brand_id)->fetchOne();
            }
            if (empty($currentBrand)) {
                $currentBrand = \MultibrandFunctionality\app\models\Brand::factory()->getDefaultOne();
            }
            $brandData['systemURL'] = $currentBrand->systemURL;
            return $brandData['systemURL'];

        } elseif (file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'Multibrand' . DIRECTORY_SEPARATOR . 'Loader.php') && !empty($mb2x)) {

            require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'Multibrand' . DIRECTORY_SEPARATOR . 'Loader.php';
            new \MGModule\Multibrand\Loader();

            $hotingRel = DataBase::table('Multibrand_Relations')->where('type', 'hosting')->where('relid', $id)->first();
            $brandID = false;
            if (isset($hotingRel->brand_id) && !empty($hotingRel->brand_id)) {
                $brandID = $hotingRel->brand_id;
            }
            if ($brandID === false) {
                $brand = DataBase::table('Multibrand_Brands')->first();
                $brandID = $brand->id;
            }
            $settings = [];
            $Brand = DataBase::table('Multibrand_Brands')->where('id', $brandID)->first();
            $settings['domain'] = $Brand->domain;
            $systemURL = \MGModule\Multibrand\Core\Server::getSystemURL($settings['domain']);
            $brandData['systemURL'] = $systemURL;
            return $brandData['systemURL'];

        } else {

            $brandData['systemURL'] = $systemURL;
            return $brandData['systemURL'];
        }
    }

}