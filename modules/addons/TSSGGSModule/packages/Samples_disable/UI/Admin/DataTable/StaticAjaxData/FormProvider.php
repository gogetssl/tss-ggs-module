<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class FormProvider extends CrudProvider
{
    public function create()
    {
        return (new Response())
            ->setActions([
                (new Reload((new DataTable())))->withParams([
                    'recordToAdd' => rand(0, 100)
                ]), new ModalClose()]);
    }
}