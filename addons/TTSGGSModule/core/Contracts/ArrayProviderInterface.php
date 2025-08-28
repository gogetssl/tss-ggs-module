<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts;

interface ArrayProviderInterface extends RecordsListProviderInterface
{
    public function setData(array $data): self;
}
