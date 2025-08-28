<?php

namespace ModulesGarden\TTSGGSModule\Components\DataTable;

use Exception;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\RecordsListProviderInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\DataSet;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

abstract class DataTable extends AbstractComponent
{
    use AjaxTrait;
    use ComponentsContainerTrait;
    use TitleTrait;
    use AjaxDataProviderTrait;

    public const COMPONENT = 'DataTable';

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var RecordsListProviderInterface
     */
    protected $dataProvider = null;

    /**
     * @var DataSet
     */
    protected $dataSet = null;

    /**
     * @var bool
     */
    protected $search = true;

    protected $recordsPerPageOptions = [
        5,
        10,
        20,
        50,
        100
    ];

    protected int $recordsPerPage = 10;

    protected string $uniqueColumnName = 'id';


    /**
     * DataTable constructor.
     * @param null $id
     */
    public function __construct()
    {
        parent::__construct();

        $this->setSlot('columns', true);
        $this->setSlot('search');
        $this->setSlot('recordsPerPageOptions');
        $this->setSlot('recordsPerPage');
        $this->setSlot('uniqueColumnName');
        $this->setTranslations([
            'search_placeholder',
            'nodata',
            'items_selected'
        ]);
    }

    /**
     * @param $button
     * @return $this
     */
    public function addActionButton($button): self
    {
        $this->addComponent('buttons', $button);

        return $this;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function addColumn(Column $column): self
    {
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * @param $button
     * @return $this
     */
    public function addMassActionButton($button): self
    {
        $this->addComponent('mass', $button);

        return $this;
    }

    /**
     * @param $component
     * @return void
     * @deprecated
     */
    public function addButton($component)
    {
        $this->addToToolbar($component);
    }

    /**
     * @param ComponentInterface $component
     * @return $this
     */
    public function addToToolbar(ComponentInterface $component): self
    {
        $this->addComponent('toolbar', $component);

        return $this;
    }

    public function addToBurgerToolbar(ComponentInterface $component): self
    {
        $this->addComponent('toolbar', $component);

        return $this;
    }

    public function handleProviderAction(): ?ResponseInterface
    {
        $providerAction = strtolower(Request::get('providerAction', false));

        if (!$providerAction)
        {
            return null;
        }

        $result = $this->runProviderAction($providerAction);

        if ($result instanceof ResponseInterface)
        {
            return $result;
        }

        return null;
    }

    /**
     * @return Response|void
     * @throws Exception
     */
    public function returnAjaxData(): ResponseInterface
    {
        try
        {
            $response = $this->handleProviderAction();

            if ($response instanceof ResponseInterface)
            {
                return $response;
            }

            $this->updateComponent();
            $this->buildHtml();
            $this->loadData();
            $this->prepareDataProvider();
            $this->getDataFromDataProvider();
            $this->setDefaultDataSetModifiers();
            $this->parseDataSetRecords();

            //set default ajax data
            $this->propagateAjaxData();

            return new Response(array_merge([
                'recordsSet' => $this->dataSet,
                'columns'    => $this->columns,
                //'ajaxData'   => $this->getSlot('ajaxData'),
            ], (new DataBuilder($this))->toArray()));
        }
        catch (\Exception $ex)
        {
            return (new Response())
                ->setError($ex->getMessage());
        }
    }

    public function loadData(): void
    {
    }

    /**
     * @return void
     */
    protected function updateComponent(): void
    {
        $this->recordsPerPage = Request::get('iDisplayLength') ?? $this->recordsPerPage;
    }

    /**
     * @return void
     */
    protected function prepareDataProvider(): void
    {
        if (!$this->dataProvider->getColumns())
        {
            $this->dataProvider->setColumns($this->columns);
        }

        if (Request::get('sortBy'))
        {
            $this->dataProvider->setSortBy(Request::get('sortBy'));
            $this->dataProvider->setSortDir(Request::get('sortDir') ?? '');
        }

        $this->dataProvider->setLimit($this->recordsPerPage);
        $this->dataProvider->setOffset(Request::get('iDisplayStart') ?? 0);
        $this->dataProvider->setSearch(Request::get('sSearch') ?? '');

        if (Request::get('ajaxData')['filters'] ?? [])
        {
            foreach (Request::get('ajaxData')['filters'] as $field => $value)
            {
                $this->dataProvider->setFieldSearch($field, $value);
            }
        }
    }

    /**
     * @return void
     */
    protected function getDataFromDataProvider(): void
    {
        $this->dataSet = $this->dataProvider->getData();
    }

    /**
     * @return void
     */
    protected function parseDataSetRecords(): void
    {
        foreach (get_class_methods($this) as $method)
        {
            if (stripos($method, 'replaceField') !== false)
            {
                $this->dataSet->setFieldModifier(lcfirst(substr($method, 12)), function($fieldName, $record, $fieldValue) use ($method) {
                    return $this->$method($fieldName, $record, $fieldValue);
                });
            }
        }

        $this->dataSet->modifyRecords();
    }

    /**
     * @param RecordsListProviderInterface $dataProv
     * @return void
     */
    protected function setDataProvider(RecordsListProviderInterface $dataProv)
    {
        $this->dataProvider = $dataProv;
    }

    public function hidePagination()
    {
        $this->setSlot('hidePagination', true);
    }

    public function setDraggableRows(bool $draggable = true)
    {
        $this->setSlot('draggable', $draggable);
    }

    public function setReorderProviderAction(string $reorderProviderAction)
    {
        $this->setSlot('reorderProviderAction', $reorderProviderAction);
    }

    public function setRecordsPerPageOptions(array $recordsPerPageOptions)
    {
        $this->recordsPerPageOptions = $recordsPerPageOptions;
    }

    public function enableFilters(bool $filtersEnabled = true)
    {
        $this->setSlot('filtersEnabled', $filtersEnabled);
    }

    protected function setDefaultDataSetModifiers()
    {
        $this->setSubRowModifier();
    }

    protected function setSubRowModifier()
    {
        $this->dataSet->setFieldModifier('subrow', function($fieldName, $row, $fieldValue) {

            if ($fieldValue instanceof ComponentInterface)
            {
                $fieldValue->buildHtml();
            }
            return $fieldValue;
        });
    }
}
