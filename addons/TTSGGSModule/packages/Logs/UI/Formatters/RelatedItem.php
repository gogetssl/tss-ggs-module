<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Formatters;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Domain;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\HostingAddon;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Ticket;
use ModulesGarden\TTSGGSModule\Core\WHMCS\URL;

class RelatedItem
{
    use TranslatorTrait;

    //Types
    const TYPE_CLIENT  = 'client';
    const TYPE_SERVICE = 'service';
    const TYPE_ADDON   = 'addon';
    const TYPE_DOMAIN  = 'domain';
    const TYPE_INVOICE = 'invoice';
    const TYPE_TICKET  = 'ticket';
    const TYPE_ORDER   = 'order';

    protected array $checks = [
        'checkForClient',
        'checkForService',
        'checkForAddon',
        'checkForDomain',
        'checkForInvoice',
        'checkForTicket',
        'checkForOrder'
    ];

    public function __invoke($fieldName, $row, $fieldValue, $raw)
    {
        if (!is_array($raw->data))
        {
            return '';
        }

        foreach ($this->checks as $check)
        {
            $result = $this->$check($raw->data);
            if ($result)
            {
                return $result;
            }
        }

        return '-';
    }

    protected function checkForClient(array $data): ?string
    {
        $clientId = Arr::get($data, self::TYPE_CLIENT);
        if (!$clientId)
        {
            return null;
        }

        $client = Client::find($clientId);
        if (!$client)
        {
            return null;
        }

        return sprintf("<a href='%s'>%s</a>", URL\Admin::clientSummary($client->id), '#' . $client->id . ' ' . $client->firstname . ' ' . $client->lastname);
    }

    protected function checkForService(array $data): ?string
    {
        $serviceId = Arr::get($data, self::TYPE_SERVICE);
        if (!$serviceId)
        {
            return null;
        }

        $service = Hosting::find($serviceId);
        if (!$service)
        {
            return null;
        }

        $parameters['productselect'] = $service->id;

        $title = '#' . $service->id . ' ' . $service->product->name . (!empty($service->domain) ? " ({$service->domain})" : "");

        return sprintf("<a href='%s'>%s</a>", URL\Admin::clientServices($service->userid, $parameters), $title);
    }

    protected function checkForAddon(array $data): ?string
    {
        $addonId = Arr::get($data, self::TYPE_ADDON);
        if (!$addonId)
        {
            return null;
        }

        $addon = HostingAddon::find($addonId);
        if (!$addon)
        {
            return null;
        }

        $parameters['productselect'] = 'a' . $addon->id;

        return sprintf("<a href='%s'>%s</a>", URL\Admin::clientServices($addon->userid, $parameters), '#' . $addon->id . ' ' . $addon->addon->name);
    }

    protected function checkForDomain(array $data): ?string
    {
        $domainId = Arr::get($data, self::TYPE_DOMAIN);
        if (!$domainId)
        {
            return null;
        }

        $domain = Domain::find($domainId);
        if (!$domain)
        {
            return null;
        }

        $parameters['id'] = $domain->id;

        return sprintf("<a href='%s'>%s</a>", URL\Admin::clientDomains($domain->userid, $parameters), '#' . $domain->id . ' ' . $domain->domain);
    }

    protected function checkForInvoice(array $data): ?string
    {
        $invoiceId = Arr::get($data, self::TYPE_INVOICE);
        if (!$invoiceId)
        {
            return null;
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice)
        {
            return null;
        }

        $invoiceNumber = !empty(trim($invoice->invoicenum)) ? $invoice->invoicenum : $invoice->id;

        return sprintf("<a href='%s'>%s</a>", URL\Admin::invoices($invoice->id), '#' . $invoiceNumber . " " . $this->translate(self::TYPE_INVOICE));
    }

    protected function checkForTicket(array $data): ?string
    {
        $ticketId = Arr::get($data, self::TYPE_TICKET);
        if (!$ticketId)
        {
            return null;
        }

        $ticket = Ticket::find($ticketId);
        if (!$ticket)
        {
            return null;
        }

        return sprintf("<a href='%s'>%s</a>", URL\Admin::tickets($ticket->id), '#' . $ticket->id . " " . $this->translate(self::TYPE_TICKET));
    }

    protected function checkForOrder(array $data): ?string
    {
        $orderId = Arr::get($data, self::TYPE_ORDER);
        if (!$orderId)
        {
            return null;
        }

        $order = Order::find($orderId);
        if (!$order)
        {
            return null;
        }

        return sprintf("<a href='%s'>%s</a>", URL\Admin::orders($order->id), '#' . $order->id . " " . $this->translate(self::TYPE_ORDER));
    }
}