<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS;

/**
 * @deprecated - use URL\Admin
 */
class Url
{
    public static function adminSummary(int $adminId, array $parameters = [])
    {
        $parameters['action'] = 'manage';
        $parameters['id'] = $adminId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('configadmins.php', $parameters);
    }

    public static function clientSummary(int $clientId, array $parameters = [])
    {
        $parameters['userid'] = $clientId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('clientssummary.php', $parameters);
    }

    public static function clientServices(int $clientId, array $parameters = [])
    {
        $parameters['userid'] = $clientId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('clientsservices.php', $parameters);
    }

    public static function clientDomains(int $clientId, array $parameters = [])
    {
        $parameters['userid'] = $clientId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('clientsdomains.php', $parameters);
    }

    public static function invoices(int $invoiceId, array $parameters = [])
    {
        $parameters['action'] = 'edit';
        $parameters['id'] = $invoiceId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('invoices.php', $parameters);
    }

    public static function tickets(int $ticketId, array $parameters = [])
    {
        $parameters['action'] = 'view';
        $parameters['id'] = $ticketId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('supporttickets.php', $parameters);
    }

    public static function orders(int $orderId, array $parameters = [])
    {
        $parameters['action'] = 'view';
        $parameters['id'] = $orderId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('orders.php', $parameters);
    }
}