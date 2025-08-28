<?php

namespace ModulesGarden\TTSGGSModule\Core\Events\Events;

use ModulesGarden\TTSGGSModule\Core\Events\Event;
use \Symfony\Component\Translation\MessageCatalogueInterface;

class MissingTranslationDetected extends Event
{
    protected string $lang;
    protected MessageCatalogueInterface $catalogue;
    protected array $replacements;

    public function __construct(string $lang, MessageCatalogueInterface $catalogue, array $replacements)
    {
        $this->lang         = $lang;
        $this->catalogue    = $catalogue;
        $this->replacements = $replacements;
    }

    public function getLocale(): string
    {
        return $this->catalogue->getLocale();
    }

    public function getCatalog(): MessageCatalogueInterface
    {
        return $this->catalogue;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }
}