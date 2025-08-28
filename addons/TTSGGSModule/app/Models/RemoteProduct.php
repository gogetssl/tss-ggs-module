<?php

namespace ModulesGarden\TTSGGSModule\App\Models;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;

class RemoteProduct extends ExtendedEloquentModel
{

    public    $timestamps = true;
    protected $table      = 'RemoteProducts';
    protected $fillable   = ['remoteId', 'name', 'description', 'vendor', 'brand', 'validation', 'category', 'rawData'];
    protected $primaryKey = 'id';

    protected $casts = [
        'rawData' => 'array'
    ];

    static public function synchronize()
    {
        $configuredApis = Helpers::getConfiguredApis();

        foreach($configuredApis as $vendor => $api)
        {
            $remoteProducts = $api->getProducts();

            foreach($remoteProducts as $remoteProductData)
            {
                $remoteId = $remoteProductData['id'];
                $model    = self::where('remoteId', $remoteId)->where('vendor', $vendor)->first();

                if(!$model)
                {
                    $model           = new self();
                    $model->remoteId = $remoteProductData['id'];
                    $model->vendor   = $vendor; //GGS or TSS
                }

                $model->name        = $remoteProductData['label'] ?: '';
                $model->description = $remoteProductData['description'] ?: '';
                $model->brand       = $remoteProductData['brand'] ?: '';
                $model->validation  = $remoteProductData['validation'] ?: '';
                $model->category    = $remoteProductData['category'] ?: '';
                $model->rawData     = $remoteProductData;
                $model->save();
            }
        }
    }

    static public function isSynchronized($vendor = '')
    {
        if($vendor)
        {
            return (bool)self::where('vendor', $vendor)->first();
        }
        else
        {
            return (bool)self::first();
        }
    }

    public function getWhmcsProduct()
    {
        $whmcsProduct = Product::where('servertype', 'TTSGGSModule')
                               ->where('configoption1', $this->vendor)
                               ->where('configoption2', $this->remoteId)
                               ->first();

        return $whmcsProduct ?: false;
    }
}
