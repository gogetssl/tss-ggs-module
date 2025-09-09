<?php

namespace ModulesGarden\TSSGGSModule\Core\Http\Admin;

use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;

class PageNotFound extends AbstractController
{
    public function index()
    {
        $pageControler = new \ModulesGarden\TSSGGSModule\Core\App\Controllers\Http\PageNotFound();

        return $pageControler->execute();
    }
}
