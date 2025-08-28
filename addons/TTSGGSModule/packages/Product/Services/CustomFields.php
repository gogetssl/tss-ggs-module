<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Services;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\CustomField as CustomFieldModel;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\CustomFields\AbstractCustomField;

class CustomFields
{
    const TYPE_PRODUCT = 'product';
    const TYPE_ADDON = 'addon';

    protected int $relId;
    protected string $type;

    public function __construct(int $relId, string $type)
    {
        $this->relId = $relId;
        $this->type = $type;
    }

    public function createCustomField(AbstractCustomField $customField)
    {
        if ($this->customFieldExists($customField->getName(), $this->type, $this->relId))
        {
            return;
        }

        $model = new CustomFieldModel();

        $model->type         = $this->type;
        $model->relid        = $this->relId;
        $model->fieldname    = $this->isValidString($customField->getFullName()) ? $customField->getFullName() : '';
        $model->fieldtype    = $this->isValidString($customField->getFieldType()) ? $customField->getFieldType() : '';
        $model->description  = $this->isValidString($customField->getDescription()) ? $customField->getDescription() : '';
        $model->fieldoptions = $this->isValidString($customField->getFieldOptions()) ? $customField->getFieldOptions() : '';
        $model->regexpr      = $this->isValidString($customField->getRegExpr()) ? $customField->getRegExpr() : '';
        $model->adminonly    = $customField->isAdminOnly() ? 'on' : '';
        $model->required     = $customField->isRequired() ? 'on' : '';
        $model->showorder    = $customField->isShowOnOrder() ? 'on' : '';
        $model->showinvoice  = $customField->isShowOnInvoice() ? 'on' : '';
        $model->sortorder    = $customField->getSortOrder();

        $model->save();
    }

    protected function customFieldExists($fieldName = null, $type = null, $relId = null): bool
    {
        if (!$fieldName || !$type || (!$relId && $relId !== 0 & $relId !== '0'))
        {
            return false;
        }

        $rawFieldName = $this->getRawConfigOptionName($fieldName);

        return (bool)CustomFieldModel::where(function($query) use ($rawFieldName) {
            $query->where('fieldname', 'LIKE', $rawFieldName . '|%');
            $query->orWhere('fieldname', '=', $rawFieldName);
        })
            ->where('type', $type)
            ->where('relid', $relId)
            ->count();
    }

    protected function getRawConfigOptionName($name = null)
    {
        return (is_string($name) && trim($name) !== '' && stripos($name, '|') > 0) ? explode('|', $name)[0] : $name;
    }

    protected function isValidString($string = null):bool
    {
        return (is_string($string) && trim($string) !== '');
    }
}