<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Forms;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Providers\Provider;

class Form extends \ModulesGarden\TTSGGSModule\Components\Form\Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = Provider::class;
}