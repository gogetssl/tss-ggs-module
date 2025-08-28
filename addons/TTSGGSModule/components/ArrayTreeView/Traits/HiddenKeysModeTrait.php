<?php

namespace ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits;

trait HiddenKeysModeTrait
{
    //TODO na razie tylko pomysł - żeby można było dublować klucze, czyli wyświetlanie wartośći zamiast kluczy
    protected bool $hiddenKeysMode = false;

    public function enableHiddenKeysMode(bool $hiddenKeysMode = true):self
    {
        $this->hiddenKeysMode = $hiddenKeysMode;

        return $this;
    }

    public function hiddenKeysModeSlotBuilder():bool
    {
        return $this->hiddenKeysMode;
    }

}