<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnChangeItemTrait
{
    use ActionsTrait;

    public function onItemAdd(ActionInterface $action): self
    {
        $this->addAction('onItemAdd', $action);

        return $this;
    }

    public function onItemRemove(ActionInterface $action): self
    {
        $this->addAction('onItemRemove', $action);

        return $this;
    }
}