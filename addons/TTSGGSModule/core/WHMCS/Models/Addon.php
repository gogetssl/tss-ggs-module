<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Addon
 *
 * @var int id
 * @var string packages
 * @var string name
 * @var string description
 * @var string billingcycle
 * @var string tax
 * @var string showorder
 * @var string downloads
 * @var string autoactivate
 * @var string suspendproduct
 * @var int welcomeemail
 * @var int weight
 */
class Addon extends \WHMCS\Product\Addon
{
    public function customFields()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\CustomField", "relid")->where("type", "=", "addon")->orderBy("sortorder");
    }

    public function serviceAddons()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ServiceAddon", "addonid");
    }
}
