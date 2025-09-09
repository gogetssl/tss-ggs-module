<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes;

use ModulesGarden\TSSGGSModule\Core\Components\Traits\TranslatorTrait;
use ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypeWithModel;
use ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\RelatedItem;
use ModulesGarden\TSSGGSModule\Core\WHMCS\URL;

class Invoice extends ItemTypeWithModel
{
    use TranslatorTrait;

    protected static string $modelClass = \ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice::class;

    public function generateUrl(): string
    {
        $this->getModel();

        return URL\Admin::invoices($this->id);
    }

    public function generateName(): string
    {
        $model = $this->getModel();

        return html_entity_decode('#' . $model->invoicenum ?: $model->id . " " . $this->translate(RelatedItem::TYPE_INVOICE));
    }
}