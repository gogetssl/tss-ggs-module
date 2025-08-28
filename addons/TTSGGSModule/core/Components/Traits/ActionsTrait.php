<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

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
