<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\UI\View;

//@todo refactor me
class PageNotFound extends View implements AdminAreaInterface, ClientAreaInterface
{
    public function __construct()
    {
        parent::__construct();

        $zero = new \ModulesGarden\TSSGGSModule\Components\PageNotFound\PageNotFound();
        $this->addElement($zero);
    }
}
