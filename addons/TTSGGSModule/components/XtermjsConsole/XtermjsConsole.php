<?php

namespace ModulesGarden\TTSGGSModule\Components\XtermjsConsole;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class XtermjsConsole extends AbstractComponent
{
    public const COMPONENT = "XtermjsConsole";

    public function setWebSocketUrl(string $url) : self
    {
        $this->setSlot('websocketUrl', $url);

        return $this;
    }

    public function setInitializeCommands(array $commands) : self
    {
        $this->setSlot('commands', $commands);

        return $this;
    }
}