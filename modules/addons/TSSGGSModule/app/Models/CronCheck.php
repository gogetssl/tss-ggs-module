<?php

namespace TSSGGSModule\App\Models;


class CronCheck extends Model
{

    public    $timestamps = true;
    protected $table      = 'CronsCheck';
    protected $fillable   = ['type', 'last_run', 'last_error'];
    protected $primaryKey = 'id';

}
