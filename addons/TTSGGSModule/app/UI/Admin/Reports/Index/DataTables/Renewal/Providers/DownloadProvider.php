<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Providers;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\Renewal\Forms\GetCsvForm;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;

class DownloadProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;
    }

    public function create()
    {
        return (new Response())
            ->setSuccess($this->translate('csvExportedSuccessfully'))
            ->setActions([
                             new DownloadFileFromForm(new GetCsvForm(), $this->formData->toArray()),
                         ]);
    }
}