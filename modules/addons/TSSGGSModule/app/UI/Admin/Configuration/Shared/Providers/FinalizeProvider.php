<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Providers;

use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;

class FinalizeProvider extends CrudProvider
{
    public function read()
    {

    }

    public function update()
    {
        (new AddonModuleRepository())->finalize();
        return (new Response())->setSuccess($this->translate('successFinalize'))->setActions([new Redirect(Url::route('',
            ['module' => 'TSSGGSModule', 'mg-page' => 'home', 'mg-action' => 'index']
        ))]);

    }

}