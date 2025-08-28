<?php

namespace ModulesGarden\Servers\TTSGGSModule\app\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class SSL extends EloquentModel
{
    protected $table = 'tblsslorders';


    public static function getByServiceId($id)
    {
        return static::where('serviceid', $id)->first();
    }
}