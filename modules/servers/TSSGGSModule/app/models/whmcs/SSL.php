<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class SSL extends EloquentModel
{
    protected $table = 'tblsslorders';


    public static function getByServiceId($id)
    {
        return static::where('serviceid', $id)->first();
    }

    public function setRemoteId($id)
    {
        $this->remoteid = $id;
    }

    public function setSSLStatus($status)
    {
        $this->setConfigdataKey('ssl_status', $status);
    }

    public function setConfigdataKey($key, $value)
    {
        $c                = (array) $this->configdata;
        $c[$key]          = $value;
        $this->configdata = $c;
    }

    public function getConfigdataKey($key)
    {
        $c = (array) $this->configdata;
        return $c[$key];
    }

}