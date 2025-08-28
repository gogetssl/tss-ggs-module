<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\Support\Translations;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;
use ModulesGarden\TTSGGSModule\Packages\Logs\Enums\LogTypes;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;

class LogsTypeTranslator
{
    use TranslatorTrait;

    public function getAvailableTranslated(): array
    {
        $translated = [];
        foreach (LogTypes::getAvailable() as $type)
        {
            $translated[$type] = $this->translate($type);
        }
        return $translated;
    }

    public function getUsedTranslated(): array
    {
        $translated = [];
        foreach (Logs::select('type')->distinct()->get() as $row)
        {
            $translated[$row->type] = $this->translate($row->type);
        }
        return $translated;
    }

    //TODO rework $type to enum
    public function translateType(string $type):string
    {
        return $this->translate($type);
    }
}