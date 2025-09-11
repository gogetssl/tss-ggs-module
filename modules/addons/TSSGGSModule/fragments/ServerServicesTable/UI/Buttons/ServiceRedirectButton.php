<?php

namespace ModulesGarden\TSSGGSModule\Fragments\ServerServicesTable\UI\Buttons;

use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;

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