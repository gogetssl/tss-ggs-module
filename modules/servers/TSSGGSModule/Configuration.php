<?php

namespace ModulesGarden\Servers\TSSGGSModule;

use ModulesGarden\Servers\TSSGGSModule\core\process\AbstractConfiguration;

class Configuration extends AbstractConfiguration
{
    public $debug = false;
    public $systemName = 'TSSGGSModule';
    public $name = 'GoGetSSL Module';
    public $description = '';
    public $clientareaName = 'GoGetSSL Module';
    public $encryptHash = 'uUc1Y8cWxDOGaby11lBwelqzo6PGMTA0dbHaKQ109olufoJgIFBOgmReKCZbpCYpASnrtfjmCIUyplaBJaUh40auDALprOHtj1g92ZRBS6S94IbZWaeZRYkG1f81h6qLMYEOr016RurCnmodFCWdMkTqrlVBvH249gzXPduKQVXpN9hooComaRPY5jZD6s8GdfR5E_BNP3v8Ui8RrdqMPST_8quMW48LhHY88xCvSWwDNjkC2tCAaK67Id2NjzIdoNTHUMISRg81nHX8ZGcbP74mxixo_ASd8YoWnDCAs8yiT4t0PwKRO_y3C1kDo69Nxz1YYt4tY1VzOD_DFBulAA5NCJLfogroo';
    public $version = '1.3.0';
    public $author = 'ModulesGarden';

    public $tablePrefix = 'TSSGGSModule_';
    public $modelRegister = [];

    function __construct()
    {
        // GoGetSSL Module / TheSSLStore Plugin - see modules/addons/TSSGGSModule/app/Config/edition.php
        if(class_exists('\ModulesGarden\TSSGGSModule\App\Libs\Edition', false))
        {
            $this->name           = \ModulesGarden\TSSGGSModule\App\Libs\Edition::name();
            $this->clientareaName = $this->name;
        }
    }

    function getServerMenu()
    {
        return [];
    }

    public function getServerWHMCSConfig()
    {
        return [];
    }
}

