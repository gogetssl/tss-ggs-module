<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelInterface;

abstract class BaseExporter
{
    protected DataModelInterface $dataSet;

    public abstract function get(): string;

    public function __construct(DataModelInterface $dataSet)
    {
        $this->dataSet = $dataSet;
    }

    public function write(\SplFileInfo $fileInfo)
    {
        $filePath = $fileInfo->getPathname();
        $file = new \SplFileObject($filePath, 'w');

        if (!$file->isWritable()) {
            throw new \Exception("File: $filePath is not writable");
        }

        $file->fwrite($this->get());
        $file = null;
    }

    protected function createTempFile()
    {
        if (!($file = tmpfile()))
        {
            throw new \Exception('Create temporary file failure');
        }

        return $file;
    }

    protected function getContentFromTempFile($file):string
    {
        rewind($file);

        $tmpFilePath = stream_get_meta_data($file)['uri'];
        return file_get_contents($tmpFilePath);
    }
}