<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Models;

use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;

class ProductConfiguration extends ExtendedEloquentModel
{
    public $incrementing = false;
    public $timestamps = false;
    protected $casts = [
        'value' => 'array',
    ];
    protected $fillable = ['product_id', 'type', 'setting', 'value'];
    protected $primaryKey = ['product_id', 'type', 'setting'];
    protected $table = 'ProductConfiguration';

    /**
     * @param $query
     * @param $productId
     * @return mixed
     */
    public function scopeOfProductId($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * @param $query
     * @param $setting
     * @return mixed
     */
    public function scopeOfSetting($query, $setting)
    {
        return $query->where('setting', $setting);
    }

    /**
     * @param $query
     * @param $value
     * @return mixed
     */
    public function scopeOfValue($query, $value)
    {
        return $query->where('value', $value);
    }
}
