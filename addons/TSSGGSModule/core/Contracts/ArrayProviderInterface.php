<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts;

interface ArrayProviderInterface extends RecordsListProviderInterface
{
    public function setData(array $data): self;
}
