<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Providers\DownloadLogProvider;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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