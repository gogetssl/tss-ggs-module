<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TicketReplies;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TicketReplies\Widgets\TicketRepliesWidget;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new TicketRepliesWidget());
    }
}