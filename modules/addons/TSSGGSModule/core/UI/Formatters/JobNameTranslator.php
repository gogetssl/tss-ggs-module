<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Formatters;

use ModulesGarden\TSSGGSModule\Core\Components\Traits\TranslatorTrait;

class JobNameTranslator
{
    use TranslatorTrait;

    public function format($job): string
    {
        $jobNameElements = explode('@', $job);
        return $this->translate($jobNameElements[0], [], ['jobs']);
    }

}