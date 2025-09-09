<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Models;

class TicketReply extends \WHMCS\Support\Ticket\Reply
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }

    public function ticket()
    {
        return $this->belongsTo("ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Ticket", "tid");
    }
}
