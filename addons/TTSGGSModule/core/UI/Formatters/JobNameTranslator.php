<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;

class JobNameTranslator
{
    use TranslatorTrait;

    public function format($job): string
    {
        $jobNameElements = explode('@', $job);
        return $this->translate($jobNameElements[0], [], ['jobs']);
    }

}