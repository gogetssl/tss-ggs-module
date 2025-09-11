<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;

class ReloadParent extends AbstractActionInterface
{
    public function toArray(): array
    {
        return [
            'action' => 'emit',
            'event'  => 'reload-parent',
        ];
    }
}
