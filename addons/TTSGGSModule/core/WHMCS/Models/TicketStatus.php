<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

class TicketStatus extends \WHMCS\Support\Ticket\Status
{
    const STATUS_OPEN           = "Open";
    const STATUS_ANSWERED       = "Answered";
    const STATUS_CUSTOMER_REPLY = "Customer-Reply";
    const STATUS_ON_HOLD        = "On Hold";
    const STATUS_IN_PROGRESS    = "In Progress";
    const STATUS_CLOSED         = "Closed";
}