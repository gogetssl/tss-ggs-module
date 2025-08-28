<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts;

interface QueryProviderInterface extends RecordsListProviderInterface
{
    public function setQuery($query): self;
}
