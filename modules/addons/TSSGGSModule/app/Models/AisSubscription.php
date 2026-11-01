<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

use ModulesGarden\TSSGGSModule\App\Libs\SecretValue;
use ModulesGarden\TSSGGSModule\Core\Models\ExtendedEloquentModel;

class AisSubscription extends ExtendedEloquentModel
{
    public $timestamps = true;
    protected $table = 'AisSubscriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'service_id',
        'client_id',
        'api_order_id',
        'ais_item_id',
        'status',
        'login_sso_link',
        'manage_sso_link',
        'autoinstall_status',
        'period_start',
        'period_end',
        'renewal_date',
        'auto_renew',
        'cancelled_at'
    ];

    protected $casts = [
        'service_id'   => 'integer',
        'client_id'    => 'integer',
        'api_order_id' => 'integer',
        'ais_item_id'  => 'integer',
        'period_start' => 'date',
        'period_end'   => 'date',
        'renewal_date' => 'date',
        'auto_renew'   => 'boolean',
        'cancelled_at' => 'datetime'
    ];

    protected $dates = [
        'period_start',
        'period_end',
        'renewal_date',
        'cancelled_at'
    ];

    // SSO links are logins to the vendor's panel: stored encrypted (see SecretValue)
    public function setLoginSsoLinkAttribute($value)
    {
        $this->attributes['login_sso_link'] = SecretValue::seal($value);
    }

    public function getLoginSsoLinkAttribute($value)
    {
        return SecretValue::open($value);
    }

    public function setManageSsoLinkAttribute($value)
    {
        $this->attributes['manage_sso_link'] = SecretValue::seal($value);
    }

    public function getManageSsoLinkAttribute($value)
    {
        return SecretValue::open($value);
    }

    static public function getByServiceId($serviceId)
    {
        return self::where('service_id', $serviceId)->first();
    }
}
