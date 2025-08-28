<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Shared\Providers;

use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TTSGGSModule\Core\Routing\Url;

class FinalizeProvider extends CrudProvider
{
    public function read()
    {

    }

    public function update()
    {
        (new AddonModuleRepository())->finalize();
        return (new Response())->setSuccess($this->translate('successFinalize'))->setActions([new Redirect(Url::route('',
            ['module' => 'TTSGGSModule', 'mg-page' => 'home', 'mg-action' => 'index']
        ))]);

    }

}