<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnClickTrait
{
    use ActionsTrait;

    public function onClick(ActionInterface $action): self
    {
        $this->addAction('onClick', $action);

        return $this;
    }
}