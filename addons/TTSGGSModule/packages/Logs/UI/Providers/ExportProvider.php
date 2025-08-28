<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Exporters\CsvExporter;
use ModulesGarden\TTSGGSModule\Core\Exporters\DataModels\Query;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Forms\DownloadCsvForm;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Modals\ExportCsvModal;

class ExportProvider extends CrudProvider
{
    use TranslatorTrait;

    public function read()
    {
        $this->data->set('from', Logs::orderBy('date', "ASC")->first()->date);
        $this->data->set('to', Logs::orderBy('date', "DESC")->first()->date);
        $this->availableValues['types'] = (new LogsTypeTranslator)->getUsedTranslated();
    }

    public function create()
    {
        $query = Logs::select('id', 'type', 'date', 'message', 'data');

        if ($fromDate = $this->formData->get('from'))
        {
            $query->where('date', ">=", (new \DateTime($fromDate))->format('Y-m-d'));
        }

        if ($toDate = $this->formData->get('to'))
        {
            $query->where('date', "<=", (new \DateTime($toDate))->modify('+1 day')->format('Y-m-d'));
        }

        if ($types = $this->formData->get('types'))
        {
            $query->whereIn('type', $types);
        }

        try {
            if ($query->count() == 0) {
                throw new \Exception($this->translate('noLogsForThisTherms'));
            }

            $fileName = tempnam("php://temp", 'logs') . '.csv';
            (new CsvExporter(new Query($query)))->write(new \SplFileInfo($fileName));
        } catch (\Exception $ex) {
            return (new Response())->setError($ex->getMessage());
        }

        return (new Response())
            ->setSuccess($this->translate('logsExportedSuccessfully'))
            ->setActions([
                new DownloadFileFromForm(new DownloadCsvForm(), ["fileFullName" => $fileName]),
                new ModalClose(new ExportCsvModal())
            ]);
    }

}