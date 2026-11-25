<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

/**
 * An entry of the module log (table <prefix>_Logs): type (info, error, ...), date, message and the data stored with
 * it (JSON: the service, the gateway request and answer, ...). Written by App\Libs\Logs\Logger, read by LogBook.
 */
class Log extends Model
{
    protected $casts = ['data' => 'array'];
    protected $dates = ['date'];
    protected $fillable = ['id', 'type', 'date', 'message', 'data'];
    protected $guarded = ['id'];
    protected $primaryKey = 'id';
    protected $table = 'Logs';
}
