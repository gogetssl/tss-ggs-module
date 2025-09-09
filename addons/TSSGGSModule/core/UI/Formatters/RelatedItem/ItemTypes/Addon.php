<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes;

use ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypeWithModel;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\ServiceAddon;
use ModulesGarden\TSSGGSModule\Core\WHMCS\URL;

class Addon extends ItemTypeWithModel
{
    protected static string $modelClass = ServiceAddon::class;

    public function generateUrl(): string
    {
        $model = $this->getModel();

        $parameters['productselect'] = 'a' . $model->id;

        return URL\Admin::clientServices($model->userid, $parameters);
    }

    public function generateName(): string
    {
        $model = $this->getModel();

        return html_entity_decode('#' . $model->id . ' ' . $model->addon->name);
    }
}