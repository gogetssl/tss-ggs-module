<?php

namespace ModulesGarden\TTSGGSModule\Components\FileManager\Providers;

use ModulesGarden\TTSGGSModule\Components\FileManager\Source\Item;
use ModulesGarden\TTSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

abstract class FileManagerProvider extends ArrayDataProvider
{
    /**
     * @var Item[]
     */
    protected array $elements = [];
    protected array $fileDefaultButtons = [];
    protected array $directoryDefaultButtons = [];

    protected abstract function buildElements(array $pathElements):array;

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSorting('name', self::SORT_ASC);
    }

    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    public function getElements()
    {
        if (empty($elements))
        {
            $this->elements = $this->buildElements(Request::get('ajaxData')['pathElements'] ?: []);
        }

        return $this->elements;
    }

    public function setFileDefaultButtons(array $defaultActions)
    {
        $this->fileDefaultButtons = $defaultActions;
    }

    public function getFileDefaultButtons(): array
    {
        return $this->fileDefaultButtons;
    }

    public function setDirectoryDefaultButtons(array $defaultActions)
    {
        $this->directoryDefaultButtons = $defaultActions;
    }

    public function getDirectoryDefaultButtons(): array
    {
        return $this->directoryDefaultButtons;
    }
}