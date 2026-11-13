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
        $c                = $this->configdataArray();
        $c[$key]          = $value;
        $this->configdata = json_encode($c);
    }

    public function getConfigdataKey($key)
    {
        $c = $this->configdataArray();

        return isset($c[$key]) ? $c[$key] : null;
    }

    protected function configdataArray()
    {
        $value = $this->configdata;
        if(is_array($value))
        {
            return $value;
        }
        $decoded = json_decode((string)$value, true);

        return is_array($decoded) ? $decoded : [];
    }

}