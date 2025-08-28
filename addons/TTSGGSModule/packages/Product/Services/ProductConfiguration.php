<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Services;

use ModulesGarden\TTSGGSModule\Packages\Product\Models\ProductConfiguration as Model;

class ProductConfiguration
{
    public const PRODUCT_TYPE = "product";
    public const PRODUCT_ADDON_TYPE = "product_addon";

    protected int $productId;
    protected string $type;

    public function __construct(int $productId, string $type = self::PRODUCT_TYPE)
    {
        $this->productId = $productId;
        $this->type = $type;
    }

    /**
     * @return $this
     */
    public function flush(): self
    {
        Model::ofProductId($this->productId)
            ->ofType($this->type)
            ->delete();

        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $settings = Model::ofProductId($this->productId)
            ->ofType($this->type)
            ->pluck('value', 'setting')
            ->all();

        foreach ($settings as $setting => &$value)
        {
            $value = json_decode($value);
        }

        return (array)$settings;
    }

    /**
     * @param $entries
     * @return $this
     */
    public function save(array $entries): self
    {
        foreach ($entries as $name => $value)
        {
            Model::updateOrCreate(
                [
                    'product_id' => $this->productId,
                    'type' => $this->type,
                    'setting' => $name
                ],
                ['value' => json_encode($value)]
            );
        }

        return $this;
    }
}
