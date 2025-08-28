<?php

namespace ModulesGarden\TTSGGSModule\App\Repositories\Whmcs;

use ModulesGarden\TTSGGSModule\App\Models\Whmcs\Product;

class ProductRepository
{
    protected $configOptions = [
        'configoption1' => 'provider',
        'configoption2' => 'product_id',
        'configoption3' => 'brand',
        'configoption4' => 'validation',
        'configoption5' => 'dcv', // allowed method validation
        'configoption6' => 'price_auto', // on/off
        'configoption7' => 'included_san',
        'configoption8' => 'included_san_wildcard',
        'configoption9' => 'category'
    ];

    public function getProductConfiguration($productId)
    {
        $product = Product::where('id', $productId)->first()->toArray();
        $results = [];
        foreach ($product as $key => $value) {
            if (array_key_exists($key, $this->configOptions)) {
                $results[$this->configOptions[$key]] = $value;
            }
        }
        return $results;
    }

    public function updateProductConfiguration($productId, $data)
    {
        $update = [];
        foreach ($data as $key => $value) {
            $new_key = array_search ($key, $this->configOptions);
            if ($new_key !== false) {
                $update[$new_key] = $value;
            }
        }

        Product::where('id', $productId)->update($update);

        return true;
    }

    public function countProducts()
    {
        return Product::where('servertype', 'TTSGGSModule')->count();
    }

    public function createProduct($name, $description, $gid, $pricing = [])
    {
        $postData = [
            'type' => 'other',
            'gid' => $gid,
            'name' => $name,
            'description' => $description,
            'hidden' => '',
            'module' => 'TTSGGSModule'
        ];

        if(empty($pricing))
        {
            $postData['paytype'] = 'free';
            $postData['autosetup'] = 'order';
        }
        else
        {
            $postData['autosetup'] = 'payment';
            $postData['paytype'] = 'recurring';
            $postData['pricing'] = $pricing;
        }

        $results = localAPI('AddProduct', $postData);

        if(isset($results['result']) && $results['result'] == 'success')
        {
            return $results['pid'];
        }

        return false;
    }

}