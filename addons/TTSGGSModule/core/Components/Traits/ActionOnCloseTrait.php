<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnCloseTrait
{
    use ActionsTrait;

    public function onClose(ActionInterface $action): self
    {
        $this->addAction('onClose', $action);

        return $this;
    }
}