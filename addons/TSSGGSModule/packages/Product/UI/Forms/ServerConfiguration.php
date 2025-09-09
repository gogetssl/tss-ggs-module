<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\UI\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ServerConfiguration extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = \ModulesGarden\TSSGGSModule\Packages\Product\UI\Providers\ServerConfiguration::class;
}