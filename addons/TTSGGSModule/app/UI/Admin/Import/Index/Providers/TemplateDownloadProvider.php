<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Providers;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Forms\GetCsvForm;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use WHMCS\Database\Capsule;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;


class TemplateDownloadProvider extends CrudProvider
{
    public function read()
    {

    }


    public function create()
    {
        return (new Response())
            ->setSuccess($this->translate('success'))
            ->setActions([
                             new DownloadFileFromForm(new GetCsvForm(), $this->formData->toArray()),
                         ]);
    }
}