<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;

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
