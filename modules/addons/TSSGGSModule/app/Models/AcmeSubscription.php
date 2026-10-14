<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

use ModulesGarden\TSSGGSModule\Core\Models\ExtendedEloquentModel;

class AcmeSubscription extends ExtendedEloquentModel
{
    public $timestamps = true;
    protected $table = 'AcmeSubscriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'service_id',
        'client_id',
        'api_order_id',
        'acme_id',
        'status',
        'acme_account_id',
        'eab_kid',
        'eab_hmac_key',
        'server_url',
        'period_start',
        'period_end',
        'renewal_date',
        'auto_renew',
        'cancelled_at'
    ];

    protected $casts = [
        'service_id' => 'integer',
        'client_id' => 'integer',
        'api_order_id' => 'integer',
        'acme_id' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'renewal_date' => 'date',
        'auto_renew' => 'boolean',
        'cancelled_at' => 'datetime'
    ];

    protected $dates = [
        'period_start',
        'period_end',
        'renewal_date',
        'cancelled_at'
    ];

    static public function getByServiceId($serviceId)
    {
        return self::where('service_id', $serviceId)->first();
    }
}
