<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\UI\View;

//@todo refactor me
class PageNotFound extends View implements AdminAreaInterface, ClientAreaInterface
{
    public function __construct()
    {
        parent::__construct();

        $zero = new \ModulesGarden\TTSGGSModule\Components\PageNotFound\PageNotFound();
        $this->addElement($zero);
    }
}
