<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;

class VendorSelectionProvider extends CrudProvider
{
    public function read()
    {

    }

    public function update()
    {
        if(!isset($this->formData['vendor']))
        {
            return (new Response())->setError($this->translate('error_vendor'));
        }

        $vendors = [];
        $vendor = $this->formData['vendor'];

        if($vendor == 'cb0')
        {
            $vendors[] = 'tss';
        }

        if($vendor == 'cb1')
        {
            $vendors[] = 'ggs';
        }

        (new AddonModuleRepository())->saveVendor($vendors);

        return (new Response())->setSuccess($this->translate('successVendorSelection'))->setActions([new Redirect(Url::route('',
            ['module' => 'TSSGGSModule', 'mg-page' => 'configuration', 'mg-action' => 'step4']
        ))]);

    }

}