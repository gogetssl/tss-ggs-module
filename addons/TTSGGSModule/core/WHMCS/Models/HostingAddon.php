<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @deprecated - use ServiceAddon instead
 */
class HostingAddon extends EloquentModel
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
    protected $fillable = ['orderid', 'hostingid', 'addonid', 'userid', 'server', 'name', 'setupfee', 'recuring', 'billingcycle', 'tax', 'status', 'regdate', 'nextduedate', 'nextinvoicedate', 'termination_date', 'peymentmethod', 'notes'];

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
    protected $table = 'tblhostingaddons';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function addon()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Addon", 'addonid');
    }

    public function getBillingcycleFriendlyAttribute()
    {
        return $this->attributes['billingcycle'];
    }

    public function hosting()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting", 'hostingid');
    }

    public function order()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", 'orderid');
    }
}
