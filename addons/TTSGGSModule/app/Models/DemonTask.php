<?php

namespace ModulesGarden\TTSGGSModule\App\Models;

use Carbon\Carbon;
use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;

/**
 * Description of DemonTask
 *
 * @var int id
 * @var varchar(128) session_id
 * @var enum('waiting', 'ready', 'queue') status
 * @var varchar(255) service_id
 * @var timestamp created_at
 * @var timestamp updated_at
 * @var timestamp deleted_at
 *
 * @method isStatusWaiting
 * @method isStatusQueue
 * @method StatusReady
 * @method isNotOlder($seconds)
 * @method isOlder($seconds)
 */
class DemonTask extends ExtendedEloquentModel
{
    const STATUS_PROCESSING = 'processing';

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'DemonTask';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['session_id', 'status', 'service_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    public function scopeIsNotOlder($query, $seconds)
    {
        $time = Carbon::now()->subSeconds((int)$seconds)->toDateTimeString();

        return $query->where("updated_at", ">=", $time);
    }

    public function scopeIsOlder($query, $seconds)
    {
        $time = Carbon::now()->subSeconds((int)$seconds)->toDateTimeString();

        return $query->where("updated_at", "<=", $time);
    }

    public function scopeIsStatusWaiting($query)
    {
        return $query->where("status", "=", 'waiting');
    }

    public function scopeIsStatusQueue($query)
    {
        return $query->where("status", "=", 'queue');
    }

    public function scopeStatusReady($query)
    {
        return $query->where("status", "=", 'ready');
    }

    public function scopeWithSessionId($query, $sessionId)
    {
        return $query->where("session_id", "=", $sessionId);
    }

    public function scopeWithoutDelete($query)
    {
        return $query->where("deleted_at", "IS NOT", null);
    }

    public function scopeWithDelete($query)
    {
        return $query->where("deleted_at", "IS", null);
    }

    public function scopeSetLimit($query, $limit = 25, $offSet = 0)
    {
        return $query->take($limit)->offset($offSet);
    }

}

