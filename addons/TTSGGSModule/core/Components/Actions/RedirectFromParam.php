<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

class RedirectFromParam extends Redirect
{
    protected string $type = 'param';

    public function __construct(string $param, array $params = [])
    {
        parent::__construct($param, $params);
    }
}
