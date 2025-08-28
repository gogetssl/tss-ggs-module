<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Events;

interface ListenerInterface
{
    public function handle($payload = []);
}
