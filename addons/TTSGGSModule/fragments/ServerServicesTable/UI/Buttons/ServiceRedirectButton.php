<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ServerServicesTable\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Routing\Url;

class ServiceRedirectButton extends IconButton implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate("service_details"));
        $this->setIcon("information");

        $this->onClick((new Redirect(Url::adminarea('clientsservices.php'), [
            'userid' => 'client_id',
            'productselect' => 'service_id'
        ])));
    }
}