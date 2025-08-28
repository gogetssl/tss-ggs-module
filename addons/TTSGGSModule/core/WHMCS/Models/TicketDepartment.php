<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class TicketDepartment extends \WHMCS\Support\Department
{
    public function tickets()
    {
        return $this->hasMany('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Ticket', "did");
    }
}
