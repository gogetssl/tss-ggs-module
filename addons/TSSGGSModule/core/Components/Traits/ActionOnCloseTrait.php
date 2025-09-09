<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnCloseTrait
{
    use ActionsTrait;

    public function onClose(ActionInterface $action): self
    {
        $this->addAction('onClose', $action);

        return $this;
    }
}