<?php

namespace ModulesGarden\TTSGGSModule\Core\Http\Admin;

use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;

class PageNotFound extends AbstractController
{
    public function index()
    {
        $pageControler = new \ModulesGarden\TTSGGSModule\Core\App\Controllers\Http\PageNotFound();

        return $pageControler->execute();
    }
}
