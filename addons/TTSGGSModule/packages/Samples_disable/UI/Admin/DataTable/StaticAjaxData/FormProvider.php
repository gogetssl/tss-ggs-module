<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;

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