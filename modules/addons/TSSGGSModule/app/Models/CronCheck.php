<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

use ModulesGarden\TSSGGSModule\Core\Models\ExtendedEloquentModel;

class CronCheck extends ExtendedEloquentModel
{

    public    $timestamps = true;
    protected $table      = 'CronsCheck';
    protected $fillable   = ['type', 'last_run', 'last_error'];
    protected $primaryKey = 'id';

}
