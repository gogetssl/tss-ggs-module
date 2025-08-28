<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts;

interface CrudProviderInterface
{
    public function create();

    public function read();

    public function update();

    public function delete();

    public function getAvailableValuesById($name);

    public function getValueById($name);
}
