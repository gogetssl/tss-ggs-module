<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\View;

use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Core\Services\Messages;
use function ModulesGarden\TSSGGSModule\Core\make;

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