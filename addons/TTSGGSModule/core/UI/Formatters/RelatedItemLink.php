<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Addon;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Admin;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Client;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Domain;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Hosting;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Invoice;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Order;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Service;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\ItemTypes\Ticket;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\RelatedItem;

/**
 * @deprecated  - use RelatedItem class instead
 */
class RelatedItemLink
{
    const TYPE_INVOICE = 'invoice';
    const TYPE_TICKET  = 'ticket';
    const TYPE_ORDER   = 'order';

    static string $defaultReturn = '-';

    public function format($type, $id)
    {
        return RelatedItem::format($type, $id);
    }

    public function formatAdmin($id)
    {
        return RelatedItem::formatFromItem(new Admin($id));
    }

    public function formatClient($id)
    {
        return RelatedItem::formatFromItem(new Client($id));
    }

    public function formatHosting($id)
    {
        return RelatedItem::formatFromItem(new Hosting($id));
    }

    public function formatService($id)
    {
        return RelatedItem::formatFromItem(new Service($id));
    }

    public function formatDomain($id)
    {
        return RelatedItem::formatFromItem(new Domain($id));
    }

    public function formatAddon($id)
    {
        return RelatedItem::formatFromItem(new Addon($id));
    }

    public function formatTicket($id)
    {
        return RelatedItem::formatFromItem(new Ticket($id));
    }

    public function formatInvoice($id)
    {
        return RelatedItem::formatFromItem(new Invoice($id));
    }

    public function formatOrder($id)
    {
        return RelatedItem::formatFromItem(new Order($id));
    }
}