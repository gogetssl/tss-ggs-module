<?php

namespace ModulesGarden\TSSGGSModule\Components\BlockError;

use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\Div\Div;
use ModulesGarden\TSSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Exceptions\UserException;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;

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
