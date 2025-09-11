<?php

namespace ModulesGarden\TSSGGSModule\Components\FileManager\Components;

use ModulesGarden\TSSGGSModule\Components\FileManager\Providers\FileManagerProvider;
use ModulesGarden\TSSGGSModule\Components\TreeListContainer\TreeListContainer;
use ModulesGarden\TSSGGSModule\Components\TreeListItem\TreeListItem;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleBreadcrumbs;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleDataTable;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\FileManager\Components\FileManagerSampleTreeView;

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