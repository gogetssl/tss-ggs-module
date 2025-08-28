<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of CustomField
 *
 * @var id
 * @var type
 * @var relid
 * @var fieldname
 * @var fieldtype
 * @var description
 * @var fieldoptions
 * @var regexpr
 * @var adminonly
 * @var required
 * @var showorder
 * @var showinvoice
 * @var sortorder
 * @var created_at
 * @var updated_at
 */
class CustomField extends \WHMCS\CustomField
{
    public function product()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", "id", "relid");
    }

    public function addon()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon", "id", "relid");
    }

    public function customFieldValues()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\CustomFieldValue", "fieldid");
    }

    public function getValueByRelid($relid)
    {
        $field  = new CustomFieldValue();
        $result = $field->where('fieldid', $this->attributes['id'])->where('relid', $relid)->first();

        return $result->value;
    }
}
