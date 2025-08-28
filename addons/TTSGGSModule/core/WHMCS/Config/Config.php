<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Config;

use ModulesGarden\TTSGGSModule\Core\Data\Container;

class Config extends Container
{
    public function __construct()
    {
        global $CONFIG;
        parent::__construct($CONFIG);
    }
}