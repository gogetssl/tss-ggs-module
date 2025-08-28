<?php

namespace ModulesGarden\TTSGGSModule\Components\BlockError;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TTSGGSModule\Components\Div\Div;
use ModulesGarden\TTSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Exceptions\UserException;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

class BlockError extends Div implements ClientAreaInterface, AdminAreaInterface
{
    protected \Throwable $exception;

    public function setException(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function loadHtml(): void
    {
        $alert = new AlertDanger();
        $alert->setText($this->exception->getMessage());
        $this->addElement($alert);

        if (Config::get('configuration.debug', false) && !($this->exception instanceof UserException))
        {
            $pre = new PreBlock();
            $pre->setContent($this->exception->getTraceAsString());

            $this->addElement($pre);
        }
    }
}
