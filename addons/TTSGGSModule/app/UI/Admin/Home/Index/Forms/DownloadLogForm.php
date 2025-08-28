<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Providers\DownloadLogProvider;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class DownloadLogForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = DownloadLogProvider::class;
        $this->providerAction = DownloadLogProvider::ACTION_CREATE;
    }

    public function loadHtml(): void
    {

    }
}