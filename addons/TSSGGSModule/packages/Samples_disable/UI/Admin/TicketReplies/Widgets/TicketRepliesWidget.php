<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TicketReplies\Widgets;

use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Ticket;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserDelete;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserEdit;
use ModulesGarden\TSSGGSModule\Components\TicketReplyPreview\TicketReplyPreview;

class TicketRepliesWidget extends Widget implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $ticket = Ticket::find(2);

        foreach ($ticket->replies as $ticketReply)
        {
            $ticketReplyPreview = new TicketReplyPreview($ticketReply);
            $ticketReplyPreview->onChange(new Reload($this));
            $ticketReplyPreview->addEditButton(new UserEdit());
            $ticketReplyPreview->addEditButton(new UserDelete());
            $this->addElement($ticketReplyPreview);
        }
    }
}