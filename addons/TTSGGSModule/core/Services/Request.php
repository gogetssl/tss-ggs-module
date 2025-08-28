<?php

namespace ModulesGarden\TTSGGSModule\Core\Services;


class Request extends \ModulesGarden\TTSGGSModule\Core\Http\Request
{
    public function getAll()
    {
        return $this->request->all();
    }
}
