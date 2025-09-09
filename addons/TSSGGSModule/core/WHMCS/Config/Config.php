<?php

namespace ModulesGarden\TSSGGSModule\Core\WHMCS\Config;

use ModulesGarden\TSSGGSModule\Core\Data\Container;

class Config extends Container
{
    public function __construct()
    {
        global $CONFIG;
        parent::__construct($CONFIG);
    }
}