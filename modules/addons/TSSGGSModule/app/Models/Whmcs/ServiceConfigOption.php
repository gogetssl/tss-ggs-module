<?php

namespace ModulesGarden\TSSGGSModule\App\Models\Whmcs;

class ServiceConfigOption extends \WHMCS\Service\ConfigOption
{
    public function productConfigOptionSelection()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\ProductConfigOptionSub', "id", "optionid");
    }

    public function productConfigOption()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\ProductConfigOption', "id", "configid");
    }

    public function service()
    {
        return $this->hasOne('ModulesGarden\\TSSGGSModule\\App\\Models\\Whmcs\\Service', "id", "relid");
    }
}
