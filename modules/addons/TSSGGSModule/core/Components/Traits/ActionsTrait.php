<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ActionInterface;

/**
 * Trait ActionsTrait
 */
trait ActionsTrait
{
    protected function addAction(string $type, ActionInterface $action)/*: self*/
    {
        $this->pushToSlot('actions.' . $type, $action->toArray());

        return $this;
    }

    protected function clearActions()/*: self*/
    {
        $this->setSlot('actions', []);

        return $this;
    }
}
