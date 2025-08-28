<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnChangeTrait
{
    use ActionsTrait;

    public function onChange(ActionInterface $action): self
    {
        $this->addAction('onChange', $action);

        return $this;
    }
}