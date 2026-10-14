<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

use ModulesGarden\TSSGGSModule\Core\Models\ExtendedEloquentModel;

/**
 * @Table(name=SSLCENTER_acme_subscription_domains,prefixed=true)
 */
class AcmeSubscriptionDomain extends ExtendedEloquentModel
{
    public    $timestamps = true;
    protected $table      = 'AcmeSubscriptionDomains';
    protected $primaryKey = 'id';

    protected $fillable = [
        'service_id',
        'domain',
        'domain_type',
        'status',
        'added_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [

    ];

    protected $dates = [

    ];


    static public function markDomainAsRemoved($serviceId, $domain)
    {
        $now = date('Y-m-d H:i:s');
        self::where('service_id', (int)$serviceId)
            ->where('domain', strtolower(trim($domain)))
            ->where('status', 'added')
            ->update([
                         'status'     => 'removed',
                         'removed_at' => $now,
                         'updated_at' => $now,
                     ]);
    }

}
