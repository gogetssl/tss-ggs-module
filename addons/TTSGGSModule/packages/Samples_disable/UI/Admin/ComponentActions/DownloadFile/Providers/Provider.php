<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Http\BinaryFileResponse;
use ModulesGarden\TTSGGSModule\Core\Http\Response;

class Provider extends CrudProvider
{
    public function read()
    {
        return (new BinaryFileResponse(__FILE__));
    }
}