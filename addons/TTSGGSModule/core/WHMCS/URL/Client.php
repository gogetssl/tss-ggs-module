<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\URL;

class Client
{
    public static function productDetails(int $hostingId, array $parameters = [])
    {
        $parameters['action'] = 'productdetails';
        $parameters['id'] = $hostingId;

        return \ModulesGarden\TTSGGSModule\Core\Routing\Url::clientarea('clientarea.php', $parameters);
    }
}