<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class TicketReply extends \WHMCS\Support\Ticket\Reply
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }

    public function ticket()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Ticket", "tid");
    }
}
