<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\HomeContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Home extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(HomeContainer::class);
    }
}
