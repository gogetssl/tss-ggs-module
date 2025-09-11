<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers\DownloadCsvProvider;

class DownloadCsvForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = DownloadCsvProvider::class;
}