<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Product
 * @deprecated - use Service instead
 */
class Hosting extends EloquentModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['userid', 'orderid', 'packageid', 'server', 'regdate', 'domain', 'paymentmethod', 'firstpaymentamount', 'amount', 'billingcycle', 'nextduedate', 'nextinvoicedate', 'termination_date', 'completed_date', 'domainstatus', 'username', 'password', 'notes', 'subscriptionid', 'promoid', 'suspendreason', 'overideautosuspend', 'overidesuspenduntil', 'dedicatedip', 'assignedips', 'ns1', 'ns2', 'diskusage', 'disklimit', 'bwusage', 'bwlimit', 'lastupdate'];

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblhosting';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function addons()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\HostingAddon", 'hostingid');
    }

    public function cancelation()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\CancelRequest", 'relid');
    }

    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }

    public function configOptions()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\HostingConfigOption", 'relid');
    }

    public function customFieldValues()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\CustomFieldValue", "relid");
    }

    public function getBillingcycleFriendlyAttribute()
    {
        return $this->attributes['billingcycle'];
    }

    public function order()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", 'orderid');
    }

    /**
     * Get related product
     *
     * @return type
     */
    public function product()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product", 'packageid');
    }

    public function server()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Server", 'server');
    }
}
