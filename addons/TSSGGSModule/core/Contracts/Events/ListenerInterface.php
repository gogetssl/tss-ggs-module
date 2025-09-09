<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Events;

interface ListenerInterface
{
    public function handle($payload = []);
}
