<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait PreloaderTrait
{
    protected bool $showPreloader = true;

    /**
     * @param bool $showPreloader
     * @return self
     */
    public function showPreloader(bool $showPreloader): self
    {
        $this->showPreloader = $showPreloader;

        return $this;
    }

    /**
     * @return bool
     */
    public function showPreloaderSlotBuilder(): bool
    {
        return $this->showPreloader;
    }
}