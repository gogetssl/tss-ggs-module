<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TicketReplies\Widgets;

use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Ticket;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserDelete;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserEdit;
use ModulesGarden\TTSGGSModule\Components\TicketReplyPreview\TicketReplyPreview;

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