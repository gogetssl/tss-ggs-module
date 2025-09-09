<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts;

interface QueryProviderInterface extends RecordsListProviderInterface
{
    public function setQuery($query): self;
}
