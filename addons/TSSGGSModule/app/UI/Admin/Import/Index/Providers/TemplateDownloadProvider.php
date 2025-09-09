<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Providers;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\GetCsvForm;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;


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