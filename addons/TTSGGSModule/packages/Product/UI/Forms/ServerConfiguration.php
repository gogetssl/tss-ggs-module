<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\UI\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ServerConfiguration extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = \ModulesGarden\TTSGGSModule\Packages\Product\UI\Providers\ServerConfiguration::class;
}