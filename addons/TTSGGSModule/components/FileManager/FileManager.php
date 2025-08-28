<?php

namespace ModulesGarden\TTSGGSModule\Components\FileManager;

use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Components\FileManager\Providers\FileManagerProvider;
use ModulesGarden\TTSGGSModule\Components\FileManager\Source\Item;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonInfo;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

abstract class FileManager extends DataTable implements AjaxComponentInterface, AdminAreaInterface
{
    public const COMPONENT = 'FileManager';

    protected array $pathElements = [];

    public function loadHtml(): void
    {
        $this->addColumn((new Column('name'))
            ->setTitle($this->translate('name', [], ['components.file_manager.file_manager.columns']))
            ->setSearchable(true)
            ->setSortable());
        $this->addColumn((new Column('fileSize'))
            ->setTitle($this->translate('fileSize', [], ['components.file_manager.file_manager.columns']))
            ->setSearchable(true, Column::TYPE_INT)
            ->setSortable());
        $this->addColumn((new Column('modification'))
            ->setTitle($this->translate('modification', [], ['components.file_manager.file_manager.columns']))
            ->setSearchable(true)
            ->setSortable());
        $this->addColumn((new Column('filePermissions'))
            ->setTitle($this->translate('filePermissions', [], ['components.file_manager.file_manager.columns']))
            ->setSearchable(true)
            ->setSortable());

        $this->setAjaxData([
            'path'     => Request::get('ajaxData')['path'],
            'parentId' => Request::get('ajaxData')['parentId']
        ]);

        $this->pathElements = Request::get('ajaxData')['pathElements'] ?: [];

        $this->addLevelUpButton();
    }

    final function loadData(): void
    {
        $provider = $this->dataProvider();
        $provider->setData($this->buildRecords($provider->getElements()));
    }

    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('name', function($fieldName, $row, $fieldValue) {
            return $this->buildIconButton($row['element']);
        });

        $this->dataSet->modifyRecords();
    }

    protected function dataProvider(): FileManagerProvider
    {
        return $this->dataProvider ?: $this->dataProvider = new $this->dataProviderClass();
    }

    protected function buildRecords($elements): array
    {
        $results = [];

        foreach ($elements as $element)
        {
            $results[] = [
                'id'              => implode('/', $this->pathElements) . '/' . $element->getName(),
                'name'            => $element->getName(),
                'fileSize'        => $element->getFileSize(),
                'filePermissions' => $element->getFilePermissions(),
                'modification'    => $element->getModification(),
                'isDir'           => $element->isDir(),
                'element'         => $element,
                'actions'         => $element->getActionButtons() ?: ($element->isDir() ? $this->dataProvider->getDirectoryDefaultButtons() : $this->dataProvider->getFileDefaultButtons())
            ];
        }

        return $results;
    }

    protected function buildIconButton(Item $item): IconButton
    {
        $iconButton = new IconButtonInfo();
        $iconButton->setIcon($item->getIcon());
        $iconButton->setName($item->getName());
        $iconButton->setTitle("");
        $iconButton->displayWithTitle($item->getName());

        if ($clickAction = $item->getClickAction($this))
        {
            $iconButton->onClick($clickAction);
        }

        return $iconButton;
    }

    public function breadcrumbsSlotBuilder()
    {
        $pathButtons = [];
        $elements    = [];

        //Add root folder
        $pathButtons[] = (new IconButtonInfo())
            ->setIcon("home")
            ->setTitle("")
            ->onClick((new ReloadById($this->getId()))
                ->withParams(['pathElements' => $elements]));

        foreach ($this->pathElements as $pathElement)
        {
            $elements[] = $pathElement;

            $pathButtons[] = (new IconButtonInfo())
                ->setIcon("folder")
                ->setName($pathElement)
                ->setTitle("")
                ->displayWithTitle($pathElement)
                ->onClick((new ReloadById($this->getId()))
                    ->withParams(['pathElements' => $elements]));
        }

        return $pathButtons;
    }

    protected function addLevelUpButton()
    {
        $elements = $this->pathElements;
        array_pop($elements);

        $this->addComponent('levelUpButton', (new IconButtonInfo())
            ->setIcon("folder")
            ->setTitle("")
            ->displayWithTitle("..")
            ->onClick((new ReloadById($this->getId()))
                ->withParams(['pathElements' => $elements]))
        );
    }

}
