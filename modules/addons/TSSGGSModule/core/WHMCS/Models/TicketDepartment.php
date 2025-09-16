<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class TicketDepartment extends \WHMCS\Support\Department
{
    public function tickets()
    {
        return $this->hasMany('ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Ticket', "did");
    }
}
