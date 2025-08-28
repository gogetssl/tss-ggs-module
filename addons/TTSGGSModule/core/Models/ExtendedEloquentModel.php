<?php

namespace ModulesGarden\TTSGGSModule\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

/**
 * Wrapper for EloquentModel
 */
class ExtendedEloquentModel extends EloquentModel
{
    public function __construct(array $attributes = [])
    {
        $this->table = ModuleConstants::getPrefixDataBase() . $this->table;

        parent::__construct($attributes);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys))
        {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName)
        {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName))
        {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName]))
        {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
