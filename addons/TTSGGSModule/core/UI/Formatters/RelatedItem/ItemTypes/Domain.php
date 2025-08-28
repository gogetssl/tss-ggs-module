<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypeWithModel;
use ModulesGarden\TTSGGSModule\Core\WHMCS\URL;

class Domain extends ItemTypeWithModel
{
    protected static string $modelClass = \ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Domain::class;

    public function generateUrl(): string
    {
        $model = $this->getModel();

        $parameters['id'] = $model->id;

        return URL\Admin::clientDomains($model->userid, $parameters);
    }

    public function generateName(): string
    {
        $model = $this->getModel();

        return html_entity_decode('#' . $model->id . ' ' . $model->domain);
    }
}