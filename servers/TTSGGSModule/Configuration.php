<?php

namespace ModulesGarden\Servers\TTSGGSModule;

use ModulesGarden\Servers\TTSGGSModule\core\process\AbstractConfiguration;

class Configuration extends AbstractConfiguration
{
    public $debug = false;
    public $systemName = 'TTSGGSModule';
    public $name = 'The SSL Store & GoGetSSL Module';
    public $description = '';
    public $clientareaName = 'The SSL Store & GoGetSSL Module';
    public $encryptHash = 'uUc1Y8cWxDOGaby11lBwelqzo6PGMTA0dbHaKQ109olufoJgIFBOgmReKCZbpCYpASnrtfjmCIUyplaBJaUh40auDALprOHtj1g92ZRBS6S94IbZWaeZRYkG1f81h6qLMYEOr016RurCnmodFCWdMkTqrlVBvH249gzXPduKQVXpN9hooComaRPY5jZD6s8GdfR5E_BNP3v8Ui8RrdqMPST_8quMW48LhHY88xCvSWwDNjkC2tCAaK67Id2NjzIdoNTHUMISRg81nHX8ZGcbP74mxixo_ASd8YoWnDCAs8yiT4t0PwKRO_y3C1kDo69Nxz1YYt4tY1VzOD_DFBulAA5NCJLfogroo';
    public $version = '1.0.0';
    public $author = 'ModulesGarden';

    public $tablePrefix = 'TTSGGSModule_';
    public $modelRegister = [];

    function __construct() {}

    function getServerMenu()
    {
        return [];
    }

    public function getServerWHMCSConfig()
    {
        return [];
    }
}

