<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\Exceptions\ItemNotFoundById;

abstract class ItemTypeWithModel implements ItemTypeInterface
{
    protected $id;
    protected static string $modelClass;

    protected \WHMCS\Model\AbstractModel $model;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function getModel():\WHMCS\Model\AbstractModel
    {
        if (empty($this->model))
        {
            $this->loadModel();
        }

        return $this->model;
    }

    protected function loadModel()
    {
        $item = static::$modelClass::find($this->id);

        if (!$item->exists)
        {
            throw new ItemNotFoundById();
        }

        $this->model = $item;
    }
}