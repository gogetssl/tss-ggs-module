<?php

namespace ModulesGarden\TTSGGSModule\Components\OverlayComponents;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;

use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;

class OverlayComponents extends Container implements AdminAreaInterface, ClientAreaInterface
{
    public const COMPONENT = 'OverlayComponents';
}
