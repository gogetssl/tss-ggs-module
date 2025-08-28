<?php

namespace ModulesGarden\TTSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;
use ModulesGarden\TTSGGSModule\Packages\Logs\Models\Logs;
use ModulesGarden\TTSGGSModule\Packages\Logs\Support\Translations\LogsTypeTranslator;

class MassDeleteProvider extends CrudProvider
{
    public function read()
    {
        $this->availableValues['types'] = (new LogsTypeTranslator)->getUsedTranslated();
    }

    public function delete()
    {
        Validator::validate($this->formData->toArray(), [
            'delete_older_than' => ['numeric', 'min:0']
        ]);

        if (!Config::get('logs.delete_logs.enabled', true))
        {
            throw new \Exception('deletingLogsIsNotAllowed');
        }

        $types     = $this->formData['types'];
        $olderThan = (int)$this->formData['delete_older_than'];

        $logsModel = Logs::query();

        if (!empty($types))
        {
            $logsModel = $logsModel->whereIn('type', $types);
        }

        if ($olderThan > 0)
        {
            $nowDate        = new \DateTime('Now');
            $dateInterval   = new \DateInterval('P' . $olderThan . 'D');
            $dateForCompare = $nowDate->sub($dateInterval);
            $logsModel      = $logsModel->where('date', '<', $dateForCompare->format('Y-m-d'));
        }

        $logsModel->delete();
    }
}