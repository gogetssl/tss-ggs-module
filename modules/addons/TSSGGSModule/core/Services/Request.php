<?php

namespace ModulesGarden\TSSGGSModule\Core\Services;


class Request extends \ModulesGarden\TSSGGSModule\Core\Http\Request
{
    public function getAll()
    {
        return $this->request->all();
    }
}
