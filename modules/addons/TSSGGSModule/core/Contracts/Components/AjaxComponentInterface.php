<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Components;

interface AjaxComponentInterface
{
    public function loadData(): void;

    public function returnAjaxData();
}
