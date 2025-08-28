<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers\DownloadCsvProvider;

class DownloadCsvForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = DownloadCsvProvider::class;
}