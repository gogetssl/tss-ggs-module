<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypeWithModel;
use ModulesGarden\TTSGGSModule\Core\WHMCS\URL;

class Client extends ItemTypeWithModel
{
    protected static string $modelClass = \ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client::class;

    public function generateUrl(): string
    {
        $model = $this->getModel();

        return URL\Admin::clientSummary($model->id);
    }

    public function generateName(): string
    {
        $model = $this->getModel();

        return html_entity_decode('#' . $model->id . ' ' . $model->firstname . ' ' . $model->lastname . ($model->companyname ? (" (" . $model->companyname . ")") : ''));
    }
}