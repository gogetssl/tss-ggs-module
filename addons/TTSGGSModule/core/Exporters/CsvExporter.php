<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelWithHeadersInterface;

class CsvExporter extends BaseExporter
{
    public function __construct(DataModelWithHeadersInterface $dataSet)
    {
        parent::__construct($dataSet);
    }

    public function setHeaders(array $headers)
    {
        $this->dataSet->setCustomHeaders($headers);
    }

    public function get(): string
    {
        $csvFile = $this->createTempFile();

        $headers = $this->dataSet->getHeaders();

        fputcsv($csvFile, $headers);

        foreach ($this->dataSet->getContentData() as $key => $item)
        {
            fputcsv($csvFile, $this->dataSet->getItemValuesByKey($key));
        }

        return $this->getContentFromTempFile($csvFile);
    }
}