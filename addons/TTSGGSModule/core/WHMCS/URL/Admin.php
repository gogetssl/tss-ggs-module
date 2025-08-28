<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\URL;

class Admin
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

    public static function productConfig(int $productId, array $parameters = [])
    {
        $parameters['action'] = 'edit';
        $parameters['id'] = $productId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::adminarea('configproducts.php', $parameters);
    }

    public static function productAddonConfig(int $productAddonId, array $parameters = [])
    {
        $parameters['action'] = 'manage';
        $parameters['id'] = $productAddonId;

        return \ModulesGarden\DiscountCenter\Core\Routing\Url::adminarea('configaddons.php', $parameters);
    }

    public static function downloadAttachmentUrl(string $type, int $id, int $index): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::clientarea('dl.php', [
            'type' => $type,
            'id' => $id,
            'i' => $index
        ]);
    }

    public static function showAttachmentUrl(string $type, int $id, int $index): string
    {
        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::clientarea('includes/thumbnail.php', [
            "{$type}id" => $id,
            'i' => $index
        ]);
    }

    public static function deleteAttachmentUrl(int $id, int $index, int $ticketId, string $type): string
    {
        $token = \generate_token("link");

        $params = [
            'action' => 'viewticket',
            'id' => $ticketId ?: $id,
            'removeattachment' => 'true',
            'filecount' => $index,
            'idsd' => $id,
        ];
        if ($type) {
            $params['type'] = $type;
        }

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::generateUrl(\ModulesGarden\TTSGGSModule\Core\Routing\Url::getAdminFolderPath() . 'supporttickets.php', $params) . $token;
    }
}