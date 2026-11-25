<?php

namespace TSSGGSModule\App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use TSSGGSModule\App\Libs\Support\Edition;

/**
 * Base of the module's own tables: the table name gets the edition's prefix ("CronsCheck" is GGSModule_CronsCheck or
 * TSSPlugin_CronsCheck), a key made of several columns works for save(), and dates are written as Y-m-d H:i:s.
 */
class Model extends EloquentModel
{
    public function __construct(array $attributes = [])
    {
        $this->table = Edition::tablePrefix() . '_' . $this->table;
        parent::__construct($attributes);
    }

    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys))
        {
            return parent::setKeysForSaveQuery($query);
        }
        foreach($keys as $keyName)
        {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName))
        {
            $keyName = $this->getKeyName();
        }

        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
