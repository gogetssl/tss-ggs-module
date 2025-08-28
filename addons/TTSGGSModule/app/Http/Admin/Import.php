<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\ImportContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Import extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(ImportContainer::class);
    }
}
