<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\HomeContainer;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\SettingsContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Settings extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(SettingsContainer::class);
    }
}
