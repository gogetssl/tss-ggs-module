<?php

namespace ModulesGarden\TTSGGSModule\Components\FileManager\Components;

use ModulesGarden\TTSGGSModule\Components\FileManager\Providers\FileManagerProvider;
use ModulesGarden\TTSGGSModule\Components\TreeListContainer\TreeListContainer;
use ModulesGarden\TTSGGSModule\Components\TreeListItem\TreeListItem;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleBreadcrumbs;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleDataTable;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleTreeView;

abstract class FileManagerTreeView extends TreeListContainer implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $dataProviderClass;
    protected FileManagerProvider $dataProvider;

    public function loadHtml(): void
    {
        $provider = $this->dataProvider();
        $data = $provider->getElements();

        //$pathElements = explode("/", trim(Request::get('ajaxData')['path'], '/') ?? []);

        $this->buildElements($data, $this);
    }

    protected function buildElements($elements, AbstractComponent $treeListItemContainer): void
    {
        foreach ($elements as $element)
        {
            if (!$element->isDir())
            {
                continue;
            }

            $item = new TreeListItem();
            $item->setTitle($element->getName());

            $this->buildElements($element->getItems(), $item);

            $treeListItemContainer->addElement($item);
        }
    }

    protected function dataProvider()
    {
        return $this->dataProvider ?? $this->dataProvider = new $this->dataProviderClass();
    }

//    public function returnAjaxData(): ResponseInterface
//    {
//        try
//        {
//            $this->loadHtml();
//            $this->loadData();
//
//            //set default ajax data
//            $this->propagateAjaxData();
//
//            return (new Response(array_merge([
//                'ajaxData'   => $this->getSlot('ajaxData'),
//            ], (new DataBuilder($this))->toArray())))->setActions([
//                (new ReloadById((new FileManagerSampleBreadcrumbs())->getId()))->withParams(['path'=>Request::get('ajaxData')['path']]),
//                (new ReloadById((new FileManagerSampleDataTable())->getId()))->withParams(['path'=>Request::get('ajaxData')['path']]),
//            ]);
//        }
//        catch (\Exception $ex)
//        {
//            return (new Response())
//                ->setError($ex->getMessage());
//        }
//    }
}