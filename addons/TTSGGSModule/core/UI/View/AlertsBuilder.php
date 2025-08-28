<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\View;

use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Services\Messages;
use function ModulesGarden\TTSGGSModule\Core\make;

class AlertsBuilder
{
    public function create(): array
    {
        $alerts = [];
        foreach (make(Messages::class)->getAlerts() as $message)
        {
            $alerts[] = (new Alert())
                ->setText($message->getText())
                ->setType($message->getType())
                ->setOutline();
        }

        return $alerts;
    }
}