<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypeWithModel;

class ProductAddon extends ItemTypeWithModel
{
    protected static string $modelClass = \ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon::class;

    public function generateUrl(): string
    {
        return URL\Admin::productAddonConfig($this->id);
    }

    public function generateName(): string
    {
        $model = $this->getModel();

        return html_entity_decode($model->name);
    }
}