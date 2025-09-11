<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Http\BinaryFileResponse;
use ModulesGarden\TSSGGSModule\Core\Http\Response;

class Provider extends CrudProvider
{
    public function read()
    {
        return (new BinaryFileResponse(__FILE__));
    }
}