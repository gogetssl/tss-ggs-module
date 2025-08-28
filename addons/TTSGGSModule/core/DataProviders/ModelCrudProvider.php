<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelCrudProvider extends CrudProvider
{
    protected EloquentModel $model;
    protected string $key;

    public function __construct(EloquentModel|string $model, string $key = 'id')
    {
        parent::__construct();

        $this->setModel($model);
        $this->setKey($key);
    }

    public function read()
    {
        if (!$value = $this->formData->get($this->key))
        {
            return null;
        }

        $dbData = $this->model->where($this->key, $value)->first();

        if ($dbData->exists)
        {
            $this->data->createFrom($dbData->toArray());
        }
    }

    public function create()
    {
        $this->model->fill($this->formData->toArray())->save();
    }

    public function update()
    {
        $dbData = $this->model->where($this->key, $this->formData[$this->key])->first();

        if (!$dbData->exists)
        {
            throw new \Exception("Item not found");
        }

        $dbData->fill($this->formData->toArray())->save();
    }

    public function delete()
    {
        $dbData = $this->model->where($this->key, $this->formData[$this->key]);

        if ($dbData->isEmpty())
        {
            throw new \Exception("No items found");
        }

        $dbData->delete();
    }

    protected function setKey(string $key):self
    {
        $this->key = $key;

        return $this;
    }

    protected function setModel(EloquentModel|string $model)
    {
        if (is_string($model))
        {
            $model = $this->getModelObjectFromClass($model);
        }

        $this->model = $model;
    }

    protected function getModelObjectFromClass(string $modelClass):EloquentModel
    {
        if (!class_exists($modelClass))
        {
            throw new \Exception("Provided class not found: $modelClass");
        }

        $modelObject = new $modelClass();

        if (!is_subclass_of($modelObject, EloquentModel::class))
        {
            throw new \Exception("Provided class object must be instance of \Illuminate\Database\Eloquent\Model");
        }

        return $modelObject;
    }

}