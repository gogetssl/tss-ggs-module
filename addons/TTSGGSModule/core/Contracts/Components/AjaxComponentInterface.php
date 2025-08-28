<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Components;

interface AjaxComponentInterface
{
    public function loadData(): void;

    public function returnAjaxData();
}
