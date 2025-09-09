<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Exporters\CsvExporter;
use ModulesGarden\TSSGGSModule\Core\Exporters\DataModels\Query;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;
use ModulesGarden\TSSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Forms\DownloadCsvForm;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Modals\ExportCsvModal;

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