<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ActionInterface;

trait ActionOnChangeTrait
{
    use ActionsTrait;

    public function onChange(ActionInterface $action): self
    {
        $this->addAction('onChange', $action);

        return $this;
    }
}