<?php

namespace ModulesGarden\TTSGGSModule\Components\VncConsole;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class VncConsole extends AbstractComponent
{
    public const COMPONENT = "VncConsole";

    public function setWebSocketUrl(string $url) : self
    {
        $this->setSlot('websocketUrl', $url);

        return $this;
    }

    public function setPassword(string $password) : self
    {
        $this->setSlot('password', $password);

        return $this;
    }
}