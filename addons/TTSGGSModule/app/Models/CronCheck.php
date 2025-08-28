<?php

namespace ModulesGarden\TTSGGSModule\App\Models;

use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;

class CronCheck extends ExtendedEloquentModel
{

    public    $timestamps = true;
    protected $table      = 'CronsCheck';
    protected $fillable   = ['type', 'last_run', 'last_error'];
    protected $primaryKey = 'id';

}
