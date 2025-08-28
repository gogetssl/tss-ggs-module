<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;

class RedirectToPreviousPage extends AbstractActionInterface
{
    public function toArray(): array
    {
        return [
            'action' => 'redirectToPreviousPage',
        ];
    }
}
