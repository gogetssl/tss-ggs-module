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
    public $version = '1.4.1';
    public $author = 'ModulesGarden';

    /** Tables are named by the edition - see App\Libs\Edition::table() */
    public $tablePrefix = false;
    public $modelRegister = [];

    function __construct()
    {
        // GoGetSSL Module / TheSSLStore Plugin - see modules/addons/TSSGGSModule/app/Config/edition.php.
        // The name is the edition's own: it is read here even where nothing has loaded the addon yet, so that
        // neither edition can ever show the other one's name.
        $edition = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . basename(__DIR__)
            . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'Edition.php';
        if(!class_exists('\ModulesGarden\TSSGGSModule\App\Libs\Edition', false) && is_file($edition))
        {
            require_once $edition;
        }
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

